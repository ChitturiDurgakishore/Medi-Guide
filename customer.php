<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details (Updated to match your provided credentials)
$servername = "";
$username = "";
$password = "";
$dbname = "";

$mycon = mysqli_connect($servername, $username, $password, $dbname);

if (!$mycon) {
    die("Connection failed: " . mysqli_connect_error());
}

$show_form_container = true; // Flag to control the visibility of the form container
$results_displayed = false; // Flag to track if results were displayed
$searched_medicinename = ""; // Variable to store the searched medicine name

if (isset($_POST['name'])) {
    $searched_medicinename = mysqli_real_escape_string($mycon, $_POST['name']);

    // First query to fetch medicine details and alternatives from the 'alternatives' table
    $sql_alternatives = "SELECT medicinename, substitute0, substitute1, substitute2, substitute3, substitute4
                         FROM alternatives
                         WHERE medicinename = '$searched_medicinename'";
    $result_alternatives = $mycon->query($sql_alternatives);
    $alternatives_data = null;
    if ($result_alternatives) {
        $alternatives_data = mysqli_fetch_assoc($result_alternatives);
    } else {
        error_log("Error querying alternatives table: " . $mycon->error);
    }


    $medicines_to_show = [];
    if ($alternatives_data) {
        // Add the searched medicine itself to the list
        $medicines_to_show[] = $alternatives_data['medicinename'];
        // Add substitutes if they exist
        for ($i = 0; $i <= 4; $i++) {
            if (!empty($alternatives_data["substitute$i"])) {
                $medicines_to_show[] = $alternatives_data["substitute$i"];
            }
        }
        // Remove duplicates
        $medicines_to_show = array_unique($medicines_to_show);

        // Display Alternatives and Prices table
        echo "<div class='alternatives-container'>";
        echo "<h3 class='section-title'>Alternatives And Prices</h3>";
        echo "<div class='table-container'>";
        echo "<table>";
        echo "<thead><tr><th>Medicine Name</th><th>Price (Rs)</th></tr></thead>";
        echo "<tbody>";
        foreach ($medicines_to_show as $medicine) {
            // Query the 'prices' table for the price of each medicine/alternative
            $sql_price = "SELECT price FROM prices WHERE medicinename = '" . mysqli_real_escape_string($mycon, $medicine) . "'";
            $result_price = $mycon->query($sql_price);
            if ($result_price && $row_price = mysqli_fetch_assoc($result_price)) {
                echo "<tr><td>" . htmlspecialchars($medicine) . "</td><td>" . htmlspecialchars($row_price['price']) . "</td></tr>";
            } else {
                echo "<tr><td>" . htmlspecialchars($medicine) . "</td><td>Price not found</td></tr>";
            }
             // Free result set for price query
            if ($result_price) {
                mysqli_free_result($result_price);
            }
        }
        echo "</tbody></table>";
        echo "</div>"; // Close table-container
        echo "<p class='thank-you'>THANK YOU FOR USING OUR APPLICATION</p>";
        echo "</div>"; // Close alternatives-container
        $show_form_container = false;
        $results_displayed = true;
    } else {
        // If no alternatives found, still try to find pharmacies with the exact searched medicine
        echo "<p class='no-results'>No alternatives found for '" . htmlspecialchars($searched_medicinename) . "'.</p>";
        // Don't set show_form_container to true yet, as we will display the pharmacy table
    }

    // --- New Logic: Find Pharmacies with the Searched Medicine in Stock ---

    echo "<div class='pharmacies-with-stock-container'>";
    echo "<h3 class='section-title'> Available in Pharmacies </h3>";
    echo "<div class='table-container'>";
    echo "<table>";
    echo "<thead><tr><th>Pharmacy Name</th></tr></thead>";
    echo "<tbody>";

    $pharmacies_with_stock = [];

    // Query the pharmacyregistration table to get all registered pharmacies
    $sql_pharmacies = "SELECT pharmacyname FROM pharmacyregistration";
    $result_pharmacies = $mycon->query($sql_pharmacies);

    if ($result_pharmacies && $result_pharmacies->num_rows > 0) {
        while ($row_pharmacy = mysqli_fetch_assoc($result_pharmacies)) {
            $pharmacy_name = $row_pharmacy['pharmacyname'];
            // Construct the potential inventory table name
            // Use the database name prefix
            $pharmacy_inventory_table = $dbname . "." . preg_replace('/[^A-Za-z0-9_]/', '', $pharmacy_name);


            // Check if the pharmacy's inventory table exists
            // Need to check against the full table name including database prefix if necessary
            $sql_check_table = "SHOW TABLES LIKE '" . mysqli_real_escape_string($mycon, $pharmacy_inventory_table) . "'";
            $result_check_table = $mycon->query($sql_check_table);

            if ($result_check_table && $result_check_table->num_rows > 0) {
                // Table exists, now check for the medicine with quantity > 0
                // Use prepared statement for checking medicine in pharmacy table
                // Ensure 'medicine_name' is the correct column name in pharmacy tables
                $sql_check_stock = "SELECT quantity FROM `$pharmacy_inventory_table` WHERE medicine_name = ? AND quantity > 0";
                $stmt_check_stock = $mycon->prepare($sql_check_stock);

                if ($stmt_check_stock) {
                    $stmt_check_stock->bind_param("s", $searched_medicinename);
                    $stmt_check_stock->execute();
                    $result_check_stock = $stmt_check_stock->get_result();

                    if ($result_check_stock && $result_check_stock->num_rows > 0) {
                        // Medicine found with quantity > 0, add pharmacy to the list
                        $pharmacies_with_stock[] = $pharmacy_name;
                    }

                    // Free result set and close statement
                    if ($result_check_stock) {
                         mysqli_free_result($result_check_stock);
                    }
                    $stmt_check_stock->close();
                } else {
                    error_log("Error preparing stock check statement for pharmacy " . $pharmacy_name . ": " . $mycon->error);
                }
            }
             // Free result set for table check query
            if ($result_check_table) {
                mysqli_free_result($result_check_table);
            }
        }
         // Free result set for pharmacies query
        mysqli_free_result($result_pharmacies);
    } else {
         error_log("Error querying pharmacyregistration table or no pharmacies found: " . $mycon->error);
    }


    // Display the pharmacies found with stock
    if (!empty($pharmacies_with_stock)) {
        foreach ($pharmacies_with_stock as $pharmacy) {
            echo "<tr><td>" . htmlspecialchars($pharmacy) . "</td></tr>";
        }
         $results_displayed = true; // Indicate that some results were displayed
    } else {
        echo "<tr><td>No pharmacies found with '" . htmlspecialchars($searched_medicinename) . "' in stock.</td></tr>";
    }

    echo "</tbody></table>";
    echo "</div>"; // Close table-container
    echo "</div>"; // Close pharmacies-with-stock-container

     // If no alternatives were found and no pharmacies were found with stock
    if (!$alternatives_data && empty($pharmacies_with_stock)) {
         echo "<p class='error-message'>PLEASE ENTER CORRECT MEDICINE NAME</p>";
         // No suggestion tip needed here as we already showed the pharmacy table results
         $show_form_container = true; // Show form container again
    } else {
        $show_form_container = false; // Hide the form container if any results were displayed
    }


} else {
     // If the page is loaded without a POST request (initial load)
     $show_form_container = true;
}


// mysqli_close($mycon); // Move closing connection outside the if block
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MEDICINE FINDER</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600&family=Open+Sans:ital,wght@0,400;0,600;1,400&family=Roboto:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background:linear-gradient(135deg, #ff6b6b, #4bcffa);
            color: #333;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start; /* Changed to flex-start */
            min-height: 100vh;
            padding: 30px;
            box-sizing: border-box;
        }

        h4 {
            color: #ff8c00;
            margin-bottom: 30px;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
            font-size: 2em;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
            letter-spacing: 0.7px;
            background-color: #000000;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            margin-top: 0;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 35px;
            margin:20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            border:black;
            width: 95%;
            max-width: 600px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            min-height: 200px;
        }

        .alternatives-container, .pharmacies-with-stock-container {
            margin-top: 20px;
            background-color: rgba(255, 255, 255, 0.9); /* Add background to result containers */
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }


        .section-title {
            color: #3498db;
            margin-top: 20px; /* Adjusted margin */
            margin-bottom: 15px;
            text-align: center;
            font-family: 'Open Sans', sans-serif;
            font-size: 1.7em;
            font-weight: 600;
            text-shadow: 0.5px 0.5px 1px rgba(0, 0, 0, 0.05);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
            font-family: 'Roboto', sans-serif;
            font-size: 1.05em;
        }

        .search-input-wrapper {
            position: relative;
            width: 100%;
        }

        input[type="text"] {
            width: calc(100% - 22px);
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Roboto', sans-serif;
            font-size: 1em;
            box-sizing: border-box;
        }

        button {
            background: #3498db;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-size: 1.05em;
            font-weight: 500;
            transition: background 0.3s ease, transform 0.2s ease;
            margin-right: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            background: #2980b9;
            transform: scale(1.02);
        }

        #suggestions {
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 6px;
            margin-top: 3px;
            max-height: 150px;
            overflow-y: auto;
            position: absolute;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: none;
            width: calc(100% - 2px); /* Adjusted width to match input */
            left: 0;
             top: 100%; /* Position below the input */
        }

        #suggestions div {
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            color: #333;
            font-family: 'Roboto', sans-serif;
            font-size: 0.95em;
        }

        #suggestions div:hover {
            background-color: #f9f9f9;
        }

        #loadingIndicator {
            display: none;
            color: #777;
            margin-top: 10px;
            font-style: italic;
            font-family: 'Open Sans', sans-serif;
            font-size: 0.9em;
            text-align: center; /* Center loading text */
        }

        .table-container {
            overflow-x: auto;
            margin-top: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            color: #444;
            font-family: 'Roboto', sans-serif;
            font-size: 0.9em;
        }

        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
            font-family: 'Open Sans', sans-serif;
            font-size: 1em;
            text-shadow: 0.5px 0.5px 1px rgba(0, 0, 0, 0.1);
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .error-message {
            color: #e74c3c;
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
            font-family: 'Roboto', sans-serif;
            font-size: 0.9em;
        }

        .suggestion-tip { /* This message is removed as per the request */
            display: none;
        }

        .thank-you {
            color: #27ae60;
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
            font-family: 'Montserrat', sans-serif;
            font-size: 1.05em;
            text-shadow: 0.5px 0.5px 1px rgba(0, 0, 0, 0.05);
        }

        .no-results {
            color: #7f8c8d;
            margin-top: 10px;
            text-align: center;
            font-family: 'Roboto', sans-serif;
            font-size: 0.9em;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            h4 {
                display: none; /* Hide h4 on mobile */
            }

            .container {
                padding: 25px;
                margin-bottom: 25px;
                max-width: 95%;
                margin:20px;
                border: 2px solid black ;
                margin-right:10px;
            }

            .alternatives-container, .pharmacies-with-stock-container {
                 padding: 15px; /* Adjusted padding for mobile */
            }

            .section-title {
                font-size: 1.5em;
                margin-top: 15px;
                margin-bottom: 10px;
            }

            label {
                font-size: 0.95em;
                margin-bottom: 6px;
            }

            input[type="text"] {
                padding: 10px;
                margin-bottom: 12px;
                font-size: 0.95em;
            }

            button {
                font-size: 0.95em;
                padding: 10px 20px;
                margin-right: 8px;
            }

            #suggestions {
                margin-top: 4px;
                max-height: 120px;
                width: calc(100% - 2px); /* Adjusted width to match input */
            }

            #suggestions div {
                padding: 8px;
                font-size: 0.9em;
            }

            th, td {
                padding: 8px 10px;
                font-size: 0.85em;
            }

            .error-message, .thank-you, .no-results {
                font-size: 0.9em;
                margin-top: 8px;
            }
        }
    </style>

    <script>
        function showLoading() {
            document.getElementById('loadingIndicator').style.display = 'block';
        }

        function hideLoading() {
            document.getElementById('loadingIndicator').style.display = 'none';
        }

        function resetResult() {
            // Clear both result sections
            document.getElementById('alternativesResultSection').innerHTML = '';
            document.getElementById('pharmaciesResultSection').innerHTML = '';
            hideLoading(); // Hide loading initially, will be shown by form submission
        }


        function submitForm(event) {
            console.log("submitForm triggered"); // Log when the function is called
            event.preventDefault(); // This prevents the default form submission
            resetResult(); // Clear previous results and hide loading initially
            showLoading(); // Show loading when form is submitted

            let form = event.target;
            let formData = new FormData(form);

            const xhr = new XMLHttpRequest();
            xhr.open('POST', form.action, true);

            xhr.onload = function() {
                console.log("AJAX request loaded. Status:", xhr.status); // Log status on load
                hideLoading(); // Hide loading when response is received
                if (xhr.status === 200) {
                    console.log("Response received:", xhr.responseText.substring(0, 200) + '...'); // Log part of the response

                    // Assuming the PHP outputs both alternatives and pharmacies HTML
                    // We need to parse the response and put content in the correct divs
                    const responseHtml = xhr.responseText;

                    // Simple parsing: find the start and end of each section based on added container classes
                    const alternativesStart = responseHtml.indexOf('<div class=\'alternatives-container\'>');
                    const alternativesEnd = responseHtml.indexOf('</div>', alternativesStart !== -1 ? alternativesStart : 0) + '</div>'.length;

                    const pharmaciesStart = responseHtml.indexOf('<div class=\'pharmacies-with-stock-container\'>');
                    const pharmaciesEnd = responseHtml.indexOf('</div>', pharmaciesStart !== -1 ? pharmaciesStart : 0) + '</div>'.length;

                     const errorMessageIndex = responseHtml.indexOf('<p class=\'error-message\'>');
                     const noAlternativesMessageIndex = responseHtml.indexOf('<p class=\'no-results\'>');


                    if (alternativesStart !== -1 && alternativesEnd !== -1) {
                        document.getElementById('alternativesResultSection').innerHTML = responseHtml.substring(alternativesStart, alternativesEnd);
                    } else if (noAlternativesMessageIndex !== -1) {
                         // Display the "No alternatives found" message if present
                         const noAlternativesEnd = responseHtml.indexOf('</p>', noAlternativesMessageIndex) + '</p>'.length;
                         document.getElementById('alternativesResultSection').innerHTML = responseHtml.substring(noAlternativesMessageIndex, noAlternativesEnd);
                    } else {
                         document.getElementById('alternativesResultSection').innerHTML = ''; // Clear if no alternatives section found
                    }


                    if (pharmaciesStart !== -1 && pharmaciesEnd !== -1) {
                         document.getElementById('pharmaciesResultSection').innerHTML = responseHtml.substring(pharmaciesStart, pharmaciesEnd);
                    } else {
                         // If no pharmacies section is found, check for the main error message
                         if (errorMessageIndex !== -1) {
                             const errorMessageEnd = responseHtml.indexOf('</p>', errorMessageIndex) + '</p>'.length;
                             document.getElementById('pharmaciesResultSection').innerHTML = responseHtml.substring(errorMessageIndex, errorMessageEnd);
                         } else {
                            document.getElementById('pharmaciesResultSection').innerHTML = ''; // Clear if no pharmacies section found
                         }
                    }


                } else {
                    console.error('Error:', xhr.statusText);
                    document.getElementById('alternativesResultSection').innerHTML = '<p class="error-message">Error loading results.</p>';
                     document.getElementById('pharmaciesResultSection').innerHTML = ''; // Clear pharmacies section on error
                }
            };
            xhr.onerror = function() {
                console.error('Request failed.'); // Log request failure
                hideLoading();
                document.getElementById('alternativesResultSection').innerHTML = '<p class="error-message">Request failed.</p>';
                 document.getElementById('pharmaciesResultSection').innerHTML = ''; // Clear pharmacies section on error
            };
            xhr.send(formData);
        }


        function getSuggestions() {
            let input = document.getElementById('medicinename').value;
            let suggestionsDiv = document.getElementById('suggestions');

            if (input.length < 3) {
                suggestionsDiv.innerHTML = '';
                suggestionsDiv.style.display = 'none';
                return;
            }

            const xhr = new XMLHttpRequest();
            // Assuming get_suggestions.php is in the same directory or accessible path
            xhr.open('GET', 'get_suggestions.php?name=' + encodeURIComponent(input), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    suggestionsDiv.innerHTML = xhr.responseText;
                    suggestionsDiv.style.display = xhr.responseText.trim() ? 'block' : 'none';
                } else {
                    console.error('Error fetching suggestions.');
                    suggestionsDiv.innerHTML = '<p class="error-message">Error fetching suggestions.</p>';
                    suggestionsDiv.style.display = 'block';
                }
            };
            xhr.onerror = function() {
                console.error('Request failed for suggestions.');
                suggestionsDiv.innerHTML = '<p class="error-message">Request failed for suggestions.</p>';
                suggestionsDiv.style.display = 'block';
            };
            xhr.send();
        }

        function selectSuggestion(name) {
            document.getElementById('medicinename').value = name;
            document.getElementById('suggestions').innerHTML = '';
            document.getElementById('suggestions').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM fully loaded and parsed"); // Log when DOM is ready
            const searchForm = document.querySelector('form');
            if (searchForm) {
                console.log("Form found, attaching submit listener."); // Log if form is found
                searchForm.addEventListener('submit', submitForm);
            } else {
                 console.error("Form not found!"); // Log if form is not found
            }

            // Hide suggestions when clicking outside the input and suggestions box
            const medicineInput = document.getElementById('medicinename');
            const suggestionsBox = document.getElementById('suggestions');
            document.addEventListener('click', (e) => {
                if (!medicineInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                    suggestionsBox.style.display = 'none';
                }
            });
        });
    </script>
</head>
<body>
    <div style="text-align: center; font-style: italic; margin-bottom: 10px; font-size: medium; background-color: black; font-family: 'Times New Roman', Times, serif; padding: 10px; margin: 20px; color: lightgreen; border-radius:20px">
        GENERIC MEDICINE FINDER
    </div>

    <?php if ($show_form_container): ?>
        <div class="container">
            <form method="post" action="">
                <div class="search-input-wrapper">
                    <div>
                        <label for="medicinename">ENTER MEDICINE NAME</label>
                        <input type="text" name="name" id="medicinename" required autocomplete="off" onkeyup="getSuggestions()">
                        <div id="suggestions"></div>
                    </div>
                </div>
                <button type="submit" name="submit">SEARCH</button>
                <button type="reset" name="reset" onclick="resetResult(); document.getElementById('medicinename').value = '';">CLEAR</button> </form>
             <div id="loadingIndicator">Loading...</div> </div>
                 <div id="resultSection">
        <?php
        // PHP code for displaying results will output directly here
        // The JavaScript will then move the content into the appropriate divs below
        ?>
    </div>

    <div id="alternativesResultSection"></div>
    <?php endif; ?>


    <div id="pharmaciesResultSection"></div>


<?php
// Close the database connection at the very end of the script
if ($mycon) {
    mysqli_close($mycon);
}
?>
</body>
</html>

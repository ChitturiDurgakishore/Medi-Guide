<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details (Updated to match your provided credentials)
$servername = "";
$username = "";
$password = "";
$dbname = "";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: pharmacylogin.php"); // Redirect to login if not logged in
    exit();
}

// Get pharmacy name from session email (assuming it's set in login/homepage)
// We will use the table name stored in the session, which should be the pharmacy name
if (!isset($_SESSION['table_name'])) {
     // Fallback or error handling if table_name is not set in session
     error_log("inventory.php: SESSION['table_name'] not set.");
     // Attempt to get pharmacy name from email if table_name is missing
     $email = $_SESSION['email'];
     $stmt = $conn->prepare("SELECT pharmacyname FROM pharmacyregistration WHERE email = ?");
     if ($stmt) {
         $stmt->bind_param("s", $email);
         $stmt->execute();
         $stmt->bind_result($pharmacyName);
         $stmt->fetch();
         $stmt->close();
         if ($pharmacyName) {
             $_SESSION['table_name'] = $pharmacyName;
         } else {
             die("Error: Could not determine pharmacy table.");
         }
     } else {
         die("Error preparing statement to get pharmacy name: " . $conn->error);
     }
}

$pharmacy_table = $dbname . "." . $_SESSION['table_name']; // Full table name using session variable

// Function to check if the table exists (Good practice, though login should ensure this)
function tableExists($conn, $tableName) {
    $checkTableQuery = "SHOW TABLES LIKE '$tableName'";
    $checkTableResult = $conn->query($checkTableQuery);
    if ($checkTableResult === false) {
        error_log("tableExists: Error checking for table: " . $conn->error);
        return false;
    }
    return $checkTableResult->num_rows > 0;
}

// Check if the pharmacy table exists before querying
if (!tableExists($conn, $pharmacy_table)) {
    error_log("inventory.php: Pharmacy table '$pharmacy_table' does not exist.");
    die("<p style='color:red'>Error: Pharmacy inventory table '$pharmacy_table' does not exist. Please contact administrator.</p>");
}

// --- Search Functionality ---
$search_term = "";
$sql = "SELECT medicine_name, quantity, price FROM `$pharmacy_table`"; // Base query

// Check if a search term was submitted
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    // Modify the query to filter by medicine name
    $sql .= " WHERE medicine_name LIKE ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
         error_log("Error preparing search query: " . $conn->error);
         // Fallback to base query or show error
         $sql = "SELECT medicine_name, quantity, price FROM `$pharmacy_table`";
         $result = $conn->query($sql); // Execute base query
         $search_term = ""; // Reset search term if prepare fails
    } else {
        // Bind the search term with wildcards for LIKE search
        $search_param = "%" . $search_term . "%";
        $stmt->bind_param("s", $search_param);
        $stmt->execute();
        $result = $stmt->get_result(); // Get the result set
    }
} else {
    // If no search term, execute the base query
    $result = $conn->query($sql);
}

$pharmacyNameDisplay = $_SESSION['table_name']; // Use the table name for display

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - <?php echo htmlspecialchars($pharmacyNameDisplay); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f0f4c3;
            color: #333;
            line-height: 1.6;
            background-image: url(bg.jpeg);
            background-size: cover;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
            background-attachment: fixed; /* Fixed background on desktop */
        }

        h1 {
            margin: 15px auto 5px auto; /* Reduced bottom margin from 15px to 5px */
            color: #1b5e20;
            background-color: #f1f7ed;
            width: fit-content;
            padding: 10px 15px;
            border-radius: 8px;
            font-style: italic;
            text-align: center;
            font-family: 'Montserrat', sans-serif;
            font-size: 2.5em;
            font-weight: 600;
        }

        h4 {
            color: #1b5e20;
            background-color: #dcedc8;
            width: fit-content;
            border-radius: 8px;
            padding: 8px;
            margin: 10px auto 20px auto;
            text-align: center;
            font-family: 'Open Sans', sans-serif;
            font-size: 1.3em;
            font-weight: 600;
        }

        p {
            font-family: 'Open Sans', sans-serif;
            font-size: 1em;
            color: #555;
            text-align: justify;
            line-height: 1.6;
        }


        .wholepage {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            margin: 10px auto;
            padding: 15px;
            width: 95%;
            max-width: 1200px;
            background-color: transparent;
            border-radius: 12px;
            box-sizing: border-box;
        }

        .wholecontainer {
            width: 100%;
            background-color: #f1f7ed;
            border-radius: 12px;
            margin: 20px auto;
            padding: 20px;
            box-sizing: border-box;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .container1 {
            color: #333;
            padding: 20px;
            border-radius: 12px;
            margin: 20px auto;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
            max-width: 100%;
        }

        /* Search Form Styles */
        .search-form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            gap: 10px; /* Space between input and button */
            flex-wrap: wrap; /* Allow wrapping on smaller screens */
            position: relative; /* Added for positioning suggestions */
        }

        .search-form input[type="text"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-family: 'Open Sans', sans-serif;
            font-size: 1em;
            flex-grow: 1; /* Allow input to grow */
            min-width: 150px; /* Minimum width for input */
        }

         .search-form input[type="text"]:focus{
            outline: none;
            border-color: #81c784;
            box-shadow: 0 0 5px rgba(129,207,132,0.5);
        }

        /* Search Suggestions Styles */
        #search_suggestions {
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 6px;
            margin-top: 5px;
            max-height: 200px;
            overflow-y: auto;
            position: absolute;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: none; /* Hidden by default */
            width: calc(100% - 2px); /* Match input width */
            left: 0;
            top: 100%; /* Position right below the input */
            text-align: left; /* Align suggestion text left */
        }

        #search_suggestions div {
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            color: #333;
            font-family: 'Open Sans', sans-serif; /* Consistent font */
            font-size: 0.95em;
            white-space: nowrap; /* Prevent wrapping in suggestion items */
            overflow: hidden;
            text-overflow: ellipsis; /* Add ellipsis for long suggestions */
        }

        #search_suggestions div:hover {
            background-color: #f9f9f9;
        }


        .container2 {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin: 20px auto;
            width: 100%;
            max-width: 600px;
            box-sizing: border-box;
            display: none; /* Initially hidden on mobile, shown via media query */
        }

        .btn1, button {
            padding: 12px 24px;
            margin: 10px;
            background-color: #81c784;
            color: white;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-size: 1.1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .btn1:hover, button:hover {
            background-color: #66bb6a;
            transform: scale(1.05);
        }

        .btn1:not(:hover), button:not(:hover) {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        /* Table Styles - Adapted for new UI */
        table {
            border: 2px solid black; /* Added black border */
            border-collapse: collapse; /* Collapse borders */
            width: 100%; /* Make table take full width of its container */
            margin-top: 0; /* Removed top margin as container1 has padding */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Add subtle shadow */
            background-color: #fff; /* Added white background to the table */
            table-layout: auto; /* Allow table layout to be automatic */
        }

        th, td {
            padding: 8px 10px; /* Reduced padding */
            text-align: left; /* Align text left */
            border-bottom: 1px solid #ddd; /* Add border below rows */
            font-family: 'Open Sans', sans-serif; /* Consistent font */
            font-size: 0.9em; /* Reduced font size */
            white-space: normal; /* Allow text wrapping within cells */
            word-wrap: break-word; /* Break long words */
        }

        th {
            background-color: #81c784; /* Green background for headers */
            color: white; /* White text for headers */
            font-weight: bold;
            text-transform: uppercase; /* Uppercase headers */
        }

        td {
            /* Removed individual td background color */
        }

        tr:nth-child(even) td {
            background-color: #f1f1f1; /* Alternate row color */
        }

        tr:hover td {
            background-color: #e0e0e0; /* Hover effect on rows */
        }

        /* Remove original bubble styles as they are not in homepage layout */
        .bubbles {
            display: none;
        }
         .bubble {
            display: none;
        }


        /* Styles for desktop layout */
        @media (min-width: 769px) {
            body{
                background-attachment: fixed;
            }
            .wholepage {
                flex-direction: row;
                justify-content: space-between;
                align-items: stretch;
            }

            .container2 {
                flex-direction: column;
                align-items: flex-start;
                width: 200px;
                margin-right: 20px;
                display: flex;
            }

             .wholecontainer {
                flex: 1;
                margin: 20px 0;
            }

            .container1 {
                 max-width: none; /* Remove max-width restriction on desktop */
                 width: 100%; /* Allow container1 to fill wholecontainer */
                 overflow-x: auto; /* Allow horizontal scrolling on desktop if needed */
            }

            .search-form {
                 justify-content: flex-start; /* Align search form to the left on desktop */
            }


            .btn1, button {
                width: 100%;
                margin: 10px 0;
            }
            .mobile-menu{
                display: none;
             }
        }

        /* Styles for mobile layout */
        @media (max-width: 768px) {
            body{
                background-attachment: scroll;
            }
            .wholepage {
                width: 100%;
                padding: 10px;
                border-radius: 0;
                flex-direction: column;
                align-items: center;
                 margin-top: 0; /* Remove initial top margin, adjusted by mobile menu */
            }
             .wholecontainer {
                width: 100%;
                padding: 15px;
                border-radius: 0;
                box-shadow: none;
                margin-top: 80px; /* Adjusted margin to be below the fixed mobile menu */
            }

            .container1 {
                width: 100%;
                padding: 15px;
                margin: 10px auto;
                border-radius: 0;
            }

            .container2 {
                display: none;
                flex-direction: column;
                align-items: center;
                margin: 10px auto;
            }

            .btn1, button {
                margin: 10px auto;
                width: 100%;
                max-width: 300px;
            }

            /* Styles for the mobile menu */
            .mobile-menu {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                background-color: #81c784;
                color: white;
                padding: 10px;
                text-align: center;
                z-index: 10;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            }
             .mobile-menu span{
                 flex-grow: 1;
                 text-align: center;
                 font-size: 1.2em;
                 font-weight: bold;
             }


            .mobile-menu-button {
                background-color: transparent;
                border: none;
                color: white;
                font-size: 1.5em;
                cursor: pointer;
                padding: 5px;
            }

            .mobile-menu-items {
                position: fixed;
                top: 50px;
                left: 0;
                width: 100%;
                background-color: #f1f7ed;
                color: #333;
                padding: 10px;
                display: none;
                flex-direction: column;
                align-items: center;
                z-index: 10;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                animation: slideIn 0.3s ease-in-out;
            }

            .mobile-menu-items.open {
                display: flex;
            }

            @keyframes slideIn {
                from {
                    transform: translateY(-100%);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }

            .mobile-menu-items a {
                padding: 12px 24px;
                margin: 10px 0;
                color: #333;
                text-decoration: none;
                font-size: 1.1em;
                width: 100%;
                text-align: center;
                border-bottom: 1px solid #ddd;
            }
            .mobile-menu-items a:last-child{
                border-bottom: none;
            }

            .mobile-menu-items a:hover {
                background-color: #dcedc8;
                color: #1b5e20;
            }
        }
    </style>
</head>
<body>

    <div class="wholepage">
        <div class="container2">
            <div style="margin-bottom: 10px;"><button class="btn1" onclick="window.location.href='pharmacyhome.php'">HOME</button></div>
            <div style="margin-bottom: 10px;"><button onclick="window.location.href='pharmacydataentry.php'">DATA ENTRY</button></div>
            <div style="margin-bottom: 10px;"><button onclick="window.location.href='pharmacydatamodify.php'">DATA MODIFY</button></div>
            <div style="margin-bottom: 10px;"><button onclick="window.location.href='pharmacyexceldata.php'">DATA THROUGH EXCEL</button></div>
        </div>
        <div class="wholecontainer">
             <h1><?php echo htmlspecialchars($pharmacyNameDisplay); ?></h1>

             <form method="get" action="" class="search-form">
                 <input type="text" id="search_medicine_name" name="search" placeholder="Search Medicine Name..." value="<?php echo htmlspecialchars($search_term); ?>" autocomplete="off">
                 <div id="search_suggestions"></div> <button type="submit" class="btn1">Search</button>
                 <?php if (!empty($search_term)): // Show Clear button only if searching ?>
                     <button type="button" class="btn1" onclick="window.location.href='pharmacydata.php'">Clear Search</button>
                 <?php endif; ?>
             </form>

            <div class="container1">
                <?php
                if ($result && $result->num_rows > 0) { // Check if result is valid and has rows
                    echo "<table>
                            <thead>
                                <tr>
                                    <th>Medicine Name</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>";
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . htmlspecialchars($row['medicine_name']) . "</td>
                                <td>
                                    Quantity: " . htmlspecialchars($row['quantity']) . "<br>
                                    Price: " . htmlspecialchars($row['price']) . "
                                </td>
                              </tr>";
                    }

                    echo "</tbody></table>";

                } else {
                    // Display message based on whether a search was performed
                    if (!empty($search_term)) {
                        echo "<p>No results found for '" . htmlspecialchars($search_term) . "' in " . htmlspecialchars($pharmacyNameDisplay) . "'s inventory.</p>";
                    } else {
                         echo "<p>No inventory data found for " . htmlspecialchars($pharmacyNameDisplay) . ".</p>";
                    }
                }

                // Close statement if it was prepared
                if (isset($stmt) && $stmt !== false) {
                    $stmt->close();
                }

                // Close connection
                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <div class="mobile-menu">
        <button class="mobile-menu-button" id="mobile-menu-button">&#9776;</button> </div>
    <div class="mobile-menu-items" id="mobile-menu-items">
        <a href="pharmacyhome.php">HOME</a>
        <a href="pharmacydataentry.php">DATA ENTRY</a>
        <a href="pharmacydatamodify.php">DATA MODIFY</a>
        <a href="pharmacyexceldata.php">DATA THROUGH EXCEL</a>
    </div>
     <script>
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenuItems = document.getElementById('mobile-menu-items');
        const wholeContainer = document.querySelector('.wholecontainer');

        if (mobileMenuButton && mobileMenuItems) {
            mobileMenuButton.addEventListener('click', () => {
                mobileMenuItems.classList.toggle('open');
                 // Adjust margin of wholecontainer based on menu state
                if (mobileMenuItems.classList.contains('open')) {
                    wholeContainer.style.marginTop = '230px'; // Increased margin when menu is open
                } else {
                    wholeContainer.style.marginTop = '80px'; // Default margin when menu is closed
                }
            });
        }

        // --- Search Suggestions JavaScript ---
        const searchInput = document.getElementById('search_medicine_name');
        const searchSuggestionsBox = document.getElementById('search_suggestions');

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim();
            if (query.length > 0) {
                // Fetch suggestions from a backend script (e.g., get_suggestions.php)
                // This script needs to query your database for medicine names
                // that match the 'query' parameter and return them as HTML divs.
                fetch(`get_suggestions.php?name=${encodeURIComponent(query)}`)
                    .then(res => res.text())
                    .then(html => {
                        searchSuggestionsBox.innerHTML = html;
                        searchSuggestionsBox.style.display = html.trim() ? 'block' : 'none';
                    })
                    .catch(error => console.error('Error fetching search suggestions:', error));
            } else {
                searchSuggestionsBox.style.display = 'none';
            }
        });

        // Handle clicking on a suggestion
        searchSuggestionsBox.addEventListener('click', (e) => {
            if (e.target && e.target.tagName === 'DIV') {
                selectSearchSuggestion(e.target.textContent);
            }
        });

        // Function to select a suggestion and populate the input field
        function selectSearchSuggestion(name) {
            searchInput.value = name;
            searchSuggestionsBox.style.display = 'none';
            // Optional: Automatically submit the form after selecting a suggestion
            // searchInput.closest('form').submit();
        }

        // Hide suggestions when clicking outside the input and suggestions box
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchSuggestionsBox.contains(e.target)) {
                searchSuggestionsBox.style.display = 'none';
            }
        });

    </script>
</body>
</html>

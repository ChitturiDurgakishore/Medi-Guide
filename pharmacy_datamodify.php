<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details (Updated to match your provided credentials)
$servername = "sql113.infinityfree.com";
$username = "if0_37435582";
$password = "RdXGj90Owk";
$dbname = "if0_37435582_mediguide";

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
     error_log("pharmacydatamodify.php: SESSION['table_name'] not set.");
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

// Check if the pharmacy table exists before proceeding
if (!tableExists($conn, $pharmacy_table)) {
    error_log("pharmacydatamodify.php: Pharmacy table '$pharmacy_table' does not exist.");
    die("<p style='color:red'>Error: Pharmacy data modify table '$pharmacy_table' does not exist. Please contact administrator.</p>");
}

$pharmacyNameDisplay = $_SESSION['table_name']; // Use the table name for display

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Modify - <?php echo htmlspecialchars($pharmacyNameDisplay); ?></title>
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

        h2 { /* Style for the form title */
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

        h4 { /* Added h4 style from homepage (not used in this page but good to have) */
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

        p { /* Added p style from homepage */
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

        .wholecontainer { /* Added wholecontainer style from homepage */
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

        .container1 { /* Style for the form container */
            background-color: #FFFFFF; /* White background for the form area */
            padding: 20px;
            border-radius: 12px;
            margin: 20px auto;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
            max-width: 80%; /* Limit form width */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add shadow */
        }

        /* Form specific styles */
        form {
             width: 100%; /* Make form take full width of container1 */
             max-width: 500px; /* Max width for the form itself */
             margin: 0 auto; /* Center the form within container1 */
             text-align: left; /* Align form content to the left */
        }

        form div{
            margin-bottom: 15px;
            display: flex;
            flex-direction: column; /* Stack label and input */
            align-items: flex-start; /* Align items to the start */
            position: relative; /* For suggestions positioning */
        }
        form div label{
            font-weight: bold;
            color: #555;
            margin-bottom: 5px; /* Space between label and input */
            width: 100%;
        }
        form div input[type="text"],
        form div input[type="number"] { /* Style for text and number inputs */
             padding: 10px;
             border-radius: 5px;
             border: 1px solid #ccc;
             box-sizing: border-box;
             font-family: 'Open Sans',sans-serif;
             width: 100%; /* Make input take full width */
        }
         form div input:focus{
            outline: none;
            border-color: #81c784;
            box-shadow: 0 0 5px rgba(129,207,132,0.5);
        }

        form span{ /* Style for button container */
            display: flex; /* Keep flex for desktop */
            justify-content: center; /* Center buttons on desktop */
            margin-top: 20px;
            gap: 20px; /* Space between buttons on desktop */
        }


        /* Suggestions Styles (Similar to dataentry) */
        #suggestions {
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
            width: 100%; /* Match input width */
            left: 0;
            top: 100%; /* Position right below the input */
            text-align: left; /* Align suggestion text left */
        }

        #suggestions div {
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

        #suggestions div:hover {
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
            padding: 12px 24px; /* Adjusted padding to match homepage buttons */
            margin: 10px; /* Adjusted margin */
            background-color: #81c784;
            color: white;
            border: none; /* Removed border */
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

        /* Remove original bubble styles */
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
            }


            .btn1, button {
                width: 100%;
                margin: 10px 0;
                padding: 12px 24px; /* Ensure consistent padding on desktop buttons */
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

            /* Mobile specific style for button container */
            form span {
                display: block; /* Stack buttons vertically */
                text-align: center; /* Center buttons */
                margin-top: 20px;
                gap: 0; /* Remove gap on mobile */
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
          /* Success and Error Message Styles */
        .success-message, .error-message {
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
            <div style="margin-bottom: 10px;"><button onclick="window.location.href='pharmacydata.php'">INVENTORY</button></div>
        </div>
        <div class="wholecontainer"> <h1><?php echo htmlspecialchars($pharmacyNameDisplay); ?></h1>

            <div class="container1"> <h2>DATA MODIFY</h2>
                 <form method="post" action="">
                     <div>
                         <label for="medicine_name">ENTER MEDICINE NAME : </label>
                         <input type="text" id="medicine_name" name="name" required autocomplete="off">
                         <div id="suggestions"></div> </div>

                     <div>
                         <label for="count">STOCK SOLD :</label>
                         <input type="number" id="count" name="count" required min="0">
                     </div>
                     <span>
                         <button type="submit" name="submit">SUBMIT</button>
                         <button type="reset" name="clear">CLEAR</button>
                     </span>

                     <?php
                     if (isset($_POST['submit'])) {
                         // Database connection is already established at the top of the file
                         // $conn = new mysqli("localhost", "root", "", "mediguide"); // Removed duplicate connection

                         // Check connection (already done at the top)
                         // if ($conn->connect_error) {
                         //     die("Connection failed: " . $conn->connect_error);
                         // }

                         $medicinename = $_POST['name'];
                         $quantity_sold = intval($_POST['count']); // Cast to integer for safety

                         $table_name = $pharmacy_table; // Use the already determined full table name


                         // --- Security Improvement: Use prepared statements for UPDATE ---
                         // --- Modified query to only update quantity ---
                         $sql = "UPDATE `$table_name` SET quantity = quantity - ? WHERE medicine_name = ?";

                         $stmt_update = $conn->prepare($sql);

                         if ($stmt_update === false) {
                             error_log("Error preparing update statement: " . $conn->error);
                             echo "<div class='error-message'>Error preparing update statement. Please try again.</div>";
                         } else {
                             // Bind parameters: i for integer (quantity), s for string (medicinename)
                             $stmt_update->bind_param("is", $quantity_sold, $medicinename);

                             if ($stmt_update->execute()) {
                                 // Check if any rows were affected by the update
                                 if ($stmt_update->affected_rows > 0) {
                                     echo "<div class='success-message'>Data updated successfully!</div>";

                                     // Fetch and display updated quantity
                                     $select_sql = "SELECT quantity FROM `$table_name` WHERE medicine_name = ?";
                                     $stmt_select = $conn->prepare($select_sql);

                                     if ($stmt_select === false) {
                                         error_log("Error preparing select statement: " . $conn->error);
                                         // Display a message but don't die
                                     } else {
                                         $stmt_select->bind_param("s", $medicinename);
                                         $stmt_select->execute();
                                         $result_select = $stmt_select->get_result();

                                         if ($result_select->num_rows > 0) {
                                             while($row = $result_select->fetch_assoc()) {
                                                 echo "<div class='success-message'>Updated Quantity: " . htmlspecialchars($row["quantity"]) . " left</div>";
                                             }
                                         } else {
                                             // This case might happen if the medicine name didn't match any row
                                             echo "<div class='error-message'>Could not retrieve updated quantity (medicine name not found?).</div>";
                                         }
                                         $stmt_select->close();
                                     }
                                 } else {
                                     // No rows were affected, likely because the medicine name was not found
                                     echo "<div class='error-message'>Medicine '" . htmlspecialchars($medicinename) . "' not found or quantity not changed.</div>";
                                 }

                             } else {
                                 // Log the error instead of displaying directly in production
                                 error_log("Error updating data: " . $stmt_update->error);
                                 echo "<div class='error-message'>Error updating data. Please try again.</div>";
                             }
                             $stmt_update->close();
                         }

                         // $conn->close(); // Moved closing connection outside the if block
                     }
                     // Ensure connection is closed at the end of the script execution
                     if ($conn) {
                        $conn->close();
                     }
                     ?>
                 </form>
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
        <a href="pharmacydata.php">INVENTORY</a>
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

        // --- Medicine Name Suggestions JavaScript (Similar to dataentry) ---
        const medicineInput = document.getElementById('medicine_name');
        const suggestionsBox = document.getElementById('suggestions');

        medicineInput.addEventListener('input', () => {
            const query = medicineInput.value.trim();
            if (query.length > 0) {
                // Fetch suggestions from a backend script (e.g., get_suggestions.php)
                // This script needs to query your database for medicine names
                // that match the 'query' parameter and return them as HTML divs.
                fetch(`get_suggestions.php?name=${encodeURIComponent(query)}`)
                    .then(res => res.text())
                    .then(html => {
                        suggestionsBox.innerHTML = html;
                        suggestionsBox.style.display = html.trim() ? 'block' : 'none';
                    })
                    .catch(error => console.error('Error fetching suggestions:', error));
            } else {
                suggestionsBox.style.display = 'none';
            }
        });

        // Handle clicking on a suggestion
        suggestionsBox.addEventListener('click', (e) => {
            if (e.target && e.target.tagName === 'DIV') {
                selectSuggestion(e.target.textContent);
            }
        });

        // Function to select a suggestion and populate the input field
        function selectSuggestion(name) {
            medicineInput.value = name;
            suggestionsBox.style.display = 'none';
        }

        // Hide suggestions when clicking outside the input and suggestions box
        document.addEventListener('click', (e) => {
            if (!medicineInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.style.display = 'none';
            }
        });

        // Optional: Hide success/error messages on click anywhere
        document.addEventListener('click', (e) => {
            const successMessage = document.querySelector('.success-message');
            if (successMessage && successMessage.contains(e.target)) {
                 successMessage.style.display = 'none';
            }
             const errorMessage = document.querySelector('.error-message');
            if (errorMessage && errorMessage.contains(e.target)) {
                 errorMessage.style.display = 'none';
            }
        });

    </script>
</body>
</html>

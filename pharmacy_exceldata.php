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
     error_log("datathroughexcel.php: SESSION['table_name'] not set.");
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
    error_log("datathroughexcel.php: Pharmacy table '$pharmacy_table' does not exist.");
    die("<p style='color:red'>Error: Pharmacy data table '$pharmacy_table' does not exist. Please contact administrator.</p>");
}

$pharmacyNameDisplay = $_SESSION['table_name']; // Use the table name for display

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Through Excel - <?php echo htmlspecialchars($pharmacyNameDisplay); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        /* --- Styles from homepage.php (and consolidated changes) --- */
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
            position: relative; /* For suggestions positioning (not needed for file input but good practice) */
        }
        form div label{
            font-weight: bold;
            color: #555;
            margin-bottom: 5px; /* Space between label and input */
            width: 100%;
        }
        form div input[type="file"] { /* Style for file input */
             padding: 10px;
             border-radius: 5px;
             border: 1px solid #ccc;
             box-sizing: border-box;
             font-family: 'Open Sans',sans-serif;
             width: 100%; /* Make input take full width */
             background-color: #e9ecef; /* Light background for file input */
        }
         form div input[type="file"]::file-selector-button { /* Style for the file selector button */
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ccc;
            background-color: #81c784;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-right: 10px;
         }
         form div input[type="file"]::file-selector-button:hover {
             background-color: #66bb6a;
         }


        form span{ /* Style for button container */
            display: flex; /* Keep flex for desktop */
            justify-content: center; /* Center buttons on desktop */
            margin-top: 20px;
            gap: 20px; /* Space between buttons on desktop */
        }


        /* Remove original bubble styles */
        .bubbles {
            display: none;
        }
         .bubble {
            display: none;
        }


        /* --- Button Styles from homepage.php --- */
        .btn1, button { /* Combined styles for both classes */
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


            .btn1, button { /* Ensure consistent padding on desktop buttons */
                padding: 12px 24px;
                /* Removed margin: 10px 0; and width: 100%; from here */
                /* These are handled by the general button style and container2 flex */
            }

             /* Specific style for buttons within container2 on desktop */
             .container2 button {
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

            /* Mobile specific style for form button container */
            form span {
                display: block; /* Stack buttons vertically */
                text-align: center; /* Center buttons */
                margin-top: 20px;
                gap: 0; /* Remove gap on mobile */
            }

             /* Specific style for buttons within form span on mobile */
             form span button {
                 width: auto; /* Allow buttons to size based on content/padding */
                 max-width: none; /* Remove max-width restriction */
                 margin: 10px; /* Add margin back for spacing between stacked buttons */
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
        <div class="wholecontainer">
             <h1><?php echo htmlspecialchars($pharmacyNameDisplay); ?></h1>

            <div class="container1">
                 <h2> EXCEL DATA</h2>
                 <form method="post" action="" enctype="multipart/form-data">

                     <div>
                         <label for="file">SELECT FILE (.csv):</label>
                         <input type="file" id="file" name="file" accept=".csv" required>
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

                        // $pharmacyname = $_SESSION['pharmacyname']; // Use $_SESSION['table_name'] instead
                        $table_name = $pharmacy_table; // Use the already determined full table name

                        // Check if a file is uploaded
                        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                            $csvFile = $_FILES['file']['tmp_name']; // Use the temporary file path from the uploaded file

                            // Get the file extension
                            $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

                            // Validate file type
                            if ($file_ext !== 'csv') {
                                echo "<div class='error-message'>Error: Please upload a CSV file.</div>";
                            } else {
                                // Open the CSV file
                                if (($handle = fopen($csvFile, "r")) !== FALSE) {
                                    // Skip the first line if it contains headers
                                    fgetcsv($handle);

                                    // --- Security Improvement: Use prepared statements for INSERT ---
                                    // Assuming CSV columns are: Medicine Name, Quantity, Price
                                    $stmt = $conn->prepare("INSERT INTO `$table_name` (medicine_name, quantity, price) VALUES (?, ?, ?)");

                                    if ($stmt === false) {
                                        error_log("Error preparing insert statement: " . $conn->error);
                                        echo "<div class='error-message'>Error preparing database statement. Please try again.</div>";
                                    } else {
                                        $row_count = 0;
                                        $success_count = 0;
                                        $error_count = 0;

                                        // Loop through the CSV file and insert data into the database
                                        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                            $row_count++;
                                            // Check if the row has the expected number of columns (3 in this case)
                                            if (count($row) >= 3) {
                                                $medicine_name = trim($row[0]); // Trim whitespace
                                                $quantity = intval(trim($row[1])); // Cast to integer and trim
                                                $price = trim($row[2]); // Trim whitespace

                                                // Bind parameters and execute the statement
                                                // s for string (medicine_name, price), i for integer (quantity)
                                                $stmt->bind_param("sis", $medicine_name, $quantity, $price);

                                                if ($stmt->execute()) {
                                                    $success_count++;
                                                } else {
                                                    error_log("Error inserting row $row_count: " . $stmt->error);
                                                    // You could add more specific error reporting here if needed
                                                    $error_count++;
                                                }
                                            } else {
                                                error_log("Skipping row $row_count due to incorrect column count.");
                                                $error_count++;
                                            }
                                        }

                                        // Close the prepared statement
                                        $stmt->close();

                                        echo "<div class='success-message'>CSV data import finished.<br>";
                                        echo "Successfully imported: " . $success_count . " rows.<br>";
                                        if ($error_count > 0) {
                                            echo "<div class='error-message'>Errors occurred for: " . $error_count . " rows. Check server logs for details.</div>";
                                        }
                                        echo "</div>";

                                    }
                                    // Close the file
                                    fclose($handle);

                                } else {
                                    echo "<div class='error-message'>Error: Unable to open the uploaded file.</div>";
                                }
                            }
                        } else {
                            echo "<div class='error-message'>Error: No file uploaded or there was an upload error.</div>";
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

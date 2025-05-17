<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// DB credentials
$servername = "sql113.infinityfree.com";
$username = "if0_37435582";
$password = "RdXGj90Owk";
$dbname = "if0_37435582_mediguide";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: pharmacylogin.php");
    exit();
}

// Get pharmacy name from session email
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT pharmacyname FROM pharmacyregistration WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($pharmacyName);
$stmt->fetch();
$stmt->close();

if (!$pharmacyName) {
    $pharmacyName = "Pharmacy"; // fallback
}
$_SESSION['table_name'] = $pharmacyName;

// Function to check if the table exists
function tableExists($conn, $tableName) {
    $checkTableQuery = "SHOW TABLES LIKE '$tableName'";
    $checkTableResult = $conn->query($checkTableQuery);
    if ($checkTableResult === false) {
        error_log("tableExists: Error checking for table: " . $conn->error);
        return false;
    }
    return $checkTableResult->num_rows > 0;
}

$pharmacy_table = $dbname . "." . $_SESSION['table_name']; // Full table name

if (!tableExists($conn, $pharmacy_table)) {
    // Handle the error: the table doesn't exist.  This should *not* happen if your login is correct.
    error_log("pharmacydataentry.php: Table '$pharmacy_table' does not exist.");
    echo "<p style='color:red'>Error: Pharmacy table '$pharmacy_table' does not exist.  Please contact administrator.</p>";
    exit(); //  Important: Stop processing.  Don't try to insert into a missing table.
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Data Entry</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Adjusted to match homepage */
            margin: 0;
            background-color: #f0f4c3;
            color: #333;
            line-height: 1.6;
            background-image: url(bg.jpeg);
            background-size: cover;
            display: flex; /* Added flex properties from homepage */
            flex-direction: column; /* Added flex properties from homepage */
            align-items: center; /* Added flex properties from homepage */
            justify-content: flex-start; /* Added flex properties from homepage */
            min-height: 100vh; /* Added from homepage */
            padding: 20px; /* Added from homepage */
            box-sizing: border-box; /* Added from homepage */
        }

        h1 {
            margin: 15px auto;
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
         h2 { /* Kept h2 for the form title, style similar to h4 */
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


        .container1 {
            background-color: #f1f7ed; /* Changed to match homepage's inner container color */
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
            max-width: 80%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* removed duplicate box-shadow */
        }

        .container1:hover {
            background-color: #c8e6c9;
            transition: background-color 0.3s ease;
        }

        .container1:not(:hover) {
            transition: background-color 0.3s ease;
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

        .bubbles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            overflow: hidden;
            pointer-events: none;
            display: none; /* Assuming bubbles are not needed on this page */
        }

        .bubble {
            position: absolute;
            bottom: -20px;
            left: 5%;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: rgba(128, 128, 128, 0.5);
            animation: bubble 3s infinite;
        }

        .bubble:nth-child(1) {
            animation-delay: 0s;
            left: 10%;
        }

        .bubble:nth-child(2) {
            animation-delay: 0.5s;
            left: 30%;
        }

        .bubble:nth-child(3) {
            animation-delay: 1s;
            left: 50%;
        }

        .bubble:nth-child(4) {
            animation-delay: 1.5s;
            left: 70%;
        }

        .bubble:nth-child(5) {
            animation-delay: 2s;
            left: 90%;
        }

        @keyframes bubble {
            0% {
                transform: translateY(0);
                opacity: 0;
            }

            100% {
                transform: translateY(-100vh);
                opacity: 1;
            }
        }

        /* Styles for desktop layout */
        @media (min-width: 769px) {
            body{
                background-attachment: fixed; /* Ensure background is fixed on desktop */
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

             .wholecontainer { /* Added wholecontainer desktop styles from homepage */
                flex: 1;
                margin: 20px 0;
            }


            .btn1, button {
                width: 100%;
                margin: 10px 0;
            }
            .mobile-menu{
                display: none;
             }
             .innercontainer2{ /* This class is not in homepage, keeping dataentry style */
                width: 70%; /* Increased width of the form container */
                min-width: 500px; /* Ensure it has a minimum width */
             }
             .container1{
                max-width: none; /* Remove max-width restriction on desktop */
                width: 100%; /* Allow container1 to fill wholecontainer */
             }
        }

        /* Styles for mobile layout */
        @media (max-width: 768px) {
             body{
                 background-attachment: scroll; /* Ensure background scrolls on mobile */
             }
            .wholepage {
                width: 100%;
                padding: 10px;
                border-radius: 0;
                flex-direction: column;
                align-items: center;
                 margin-top: 0; /* Remove initial top margin, adjusted by mobile menu */
            }
             .wholecontainer { /* Added wholecontainer mobile styles from homepage */
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
                justify-content: space-between; /* Adjusted to match homepage mobile menu */
                align-items: center;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            }
             .mobile-menu span{ /* Added span style for title in mobile menu */
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
             .innercontainer2{ /* This class is not in homepage, keeping dataentry style */
                width: 100%;
             }
        }
        /* Specific styles for the form elements from original dataentry.php */
        .innercontainer2{
            background-color: #FFFFFF;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 70%;
            min-width: 300px;
            margin: auto;
        }
 form div{
    margin-bottom: 15px;
    display: flex;
    align-items: flex-start;
    justify-content: flex-start;
    flex-wrap: nowrap;
    flex-direction: column; /* Stack label and input on small screens */
    align-items: flex-start;
    position: relative; /* Added this line */
}
        form div label{
            font-weight: bold;
            color: #555;
            margin-bottom: 0;
            margin-right: 10px;
            width: 100%;
            text-align: left;
            flex: 0 0 auto;
            margin-bottom: 5px; /* Add space between label and input on small screens */
        }
        form div input{
             padding: 10px;
             border-radius: 5px;
             border: 1px solid #ccc;
             box-sizing: border-box;
             font-family: 'Open Sans',sans-serif;
             flex: 1 1 auto;
             width: calc(100% - 20px);
             margin-left: 0;
        }
        form div input:focus{
            outline: none;
            border-color: #81c784;
            box-shadow: 0 0 5px rgba(129,207,132,0.5);
        }
        form span{
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }
        form div div{
            margin-left: 0;
            margin-right: 0;
            flex: 1 1 auto;
            position: relative;
        }
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
            display: none;
            width: calc(100% - 2px);
            left: 0;
            top: 100%;
        }

        #suggestions div {
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.2s ease;
            color: #333;
            font-family: 'Roboto', sans-serif;
            font-size: 0.95em;
            white-space: nowrap;
        }

        #suggestions div:hover {
            background-color: #f9f9f9;
        }
        .success-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #d4edda;
            color: #155724;
            padding: 20px;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.2em;
            z-index: 1000;
        }
        .error-message{
            color: red;
        }
    </style>
</head>
<body>

    <div class="wholepage">
        <div class="container2">
            <div style="margin-bottom: 10px;"><button class="btn1" onclick="window.location.href='pharmacyhome.php'">HOME</button></div>
            <div style="margin-bottom: 10px;"><button onclick="window.location.href='pharmacydatamodify.php'">DATA MODIFY</button></div>
            <div style="margin-bottom: 10px;"><button onclick="window.location.href='pharmacyexceldata.php'">DATA THROUGH EXCEL</button></div>
            <div style="margin-bottom: 10px;"><button onclick="window.location.href='pharmacydata.php'">INVENTORY</button></div>
        </div>
        <div class="wholecontainer"> <div class="container1">
                <h2>Medicine Data Entry</h2> <form method="post" action="">
                    <div >
                        <label>Medicine Name: </label>
                        <input type="text" id="medicine_name" name="medicine_name" required autocomplete="off">
                        <div id="suggestions"></div>
                    </div>
                    <div>
                        <label>Quantity :</label>
                        <input type="number" id="quantity" name="quantity" value="0" min="0"  style="width: 80px;">
                    </div>
                     <div>
                        <label>Price :</label>
                        <input type="text" name="price" required>
                    </div>
                    <span>
                        <button type="submit" class="btn1" name="submit">SUBMIT</button>
                        <button type="reset" class="btn1">CLEAR</button>
                    </span>
                    <?php
                    if (isset($_POST['submit'])) {
                        $medicine_name = $_POST['medicine_name'];
                        $quantity = $_POST['quantity'];
                        $price = $_POST['price'];
                        $pharmacy_table =  $dbname . "." . $_SESSION['table_name'];

                        // Prepare and bind
                        $stmt_insert = $conn->prepare("INSERT INTO `$pharmacy_table` (medicine_name, quantity, price) VALUES (?, ?, ?)");
                        $stmt_insert->bind_param("sis", $medicine_name, $quantity, $price);


                        if ($stmt_insert->execute()) {
                            echo "<div class='success-message'>Data inserted successfully!</div>";
                              echo "<script>
                                setTimeout(function() {
                                    document.querySelector('.success-message').style.display = 'none';
                                }, 3000);
                                </script>";
                        } else {
                             // Log the error instead of displaying directly in production
                            error_log("Error inserting data: " . $stmt_insert->error);
                            echo "<div class='error-message'>Error inserting data. Please try again.</div>";
                        }
                         $stmt_insert->close();
                         // Re-establish connection if needed for subsequent operations, or close and let script end
                         // $conn->close(); // Moved closing connection outside the if block if more processing is expected
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

        const medicineInput = document.getElementById('medicine_name');
        const suggestionsBox = document.getElementById('suggestions');

        medicineInput.addEventListener('input', () => {
            const query = medicineInput.value.trim();
            if (query.length > 0) {
                // Ensure the get_suggestions.php file exists and handles the suggestion logic
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

    // Added event listener for clicks on suggestions box to handle suggestion selection
    suggestionsBox.addEventListener('click', (e) => {
        if (e.target && e.target.tagName === 'DIV') {
            selectSuggestion(e.target.textContent);
        }
    });


    function selectSuggestion(name) {
        medicineInput.value = name;
        suggestionsBox.style.display = 'none';
    }

    document.addEventListener('click', (e) => {
        if (!medicineInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });

    // Optional: Hide success message on click anywhere
    document.addEventListener('click', (e) => {
        const successMessage = document.querySelector('.success-message');
        if (successMessage && successMessage.contains(e.target)) {
             successMessage.style.display = 'none';
        }
    });

</script>
</body>
</html>

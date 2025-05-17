<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection details
$servername = "";
$username = "";
$password = "";
$dbname = "";

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if the user is logged in
    if (!isset($_SESSION['email'])) {
        header("Location: pharmacylogin.php"); // Redirect to login if not logged in
        exit();
    }

    $email = $_SESSION['email'];

    // Fetch pharmacy name based on the logged-in email
    $sql = "SELECT pharmacyname FROM pharmacyregistration WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($pharmacyName);
    $stmt->fetch();
    $stmt->close();

    if (!$pharmacyName) {
        // Handle the case where the pharmacy name is not found
        $pharmacyName = "Pharmacy Name Not Found"; // Or redirect to an error page
    }
    //Set the session table name.
    $_SESSION['table_name'] = $pharmacyName;

} catch (Exception $e) {
    die("An error occurred: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOME PAGE</title>
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
            justify-content: flex-start; /* Changed to flex-start */
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
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
            align-items: stretch; /* Changed to stretch */
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
            background-color: #dcedc8;
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
            .wholepage {
                flex-direction: row; /* Horizontal layout for desktop */
                justify-content: space-between; /* Space between options and container */
                align-items: stretch; /* Stretch items to container height */
            }

            .container2 {
                flex-direction: column; /* column for desktop */
                align-items: flex-start; /* Align items to the start (top) */
                width: 200px; /* Fixed width for the options column */
                margin-right: 20px; /* Add some margin to the right of options */
                display: flex; /* Ensure options are visible on desktop */
            }

            .wholecontainer {
                flex: 1; /* Allow the container to take up remaining space */
                margin: 20px 0; /* only top and bottom margin */
            }
             .btn1, button{
                width: 100%;
                margin: 10px 0;
             }
             .mobile-menu{
                display: none;
             }
        }

        /* Styles for mobile layout */
        @media (max-width: 768px) {
            .wholepage {
                width: 100%;
                padding: 10px;
                border-radius: 0;
                flex-direction: column;
                align-items: center;
            }

            .wholecontainer {
                width: 100%;
                padding: 15px;
                border-radius: 0;
                box-shadow: none;
                margin-top: 80px;
            }

            .container1 {
                width: 100%;
                padding: 15px;
                margin: 10px auto;
                border-radius: 0;
            }

            .container2 {
                display: none; /* Hide options on mobile */
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
                position: fixed; /* fixed positioning */
                top: 0;
                left: 0;
                width: 100%;
                background-color: #81c784; /* Green background */
                color: white;
                padding: 10px;
                text-align: center;
                z-index: 10; /* ensure it's above other elements */
                display: flex; /* Use flexbox for layout */
                justify-content: space-between; /* Space between title and button */
                align-items: center; /* Vertically center items */
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); /* Add a shadow */
            }

            .mobile-menu-button {
                background-color: transparent;
                border: none;
                color: white;
                font-size: 1.5em; /* Increase size for better touch */
                cursor: pointer;
                padding: 5px; /* Add some padding */
            }

            .mobile-menu-items {
                position: fixed; /* fixed positioning */
                top: 50px; /* Position below the menu bar */
                left: 0;
                width: 100%;
                background-color: #f1f7ed; /* background color */
                color: #333;
                padding: 10px;
                display: none; /* initially hidden */
                flex-direction: column;
                align-items: center;
                z-index: 10;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add a shadow */
                animation: slideIn 0.3s ease-in-out; /* Add slide-in animation */
            }

            .mobile-menu-items.open {
                display: flex; /* Show the menu items when open */
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
                border-bottom: 1px solid #ddd; /* Add a border between items */
            }
            .mobile-menu-items a:last-child{
                border-bottom: none;
            }

            .mobile-menu-items a:hover {
                background-color: #dcedc8; /* hover background color */
                color: #1b5e20;
            }
        }
    </style>
</head>
<body>
    <div class="wholepage">
        <div class="container2">
            <div><button class="btn1" onclick="window.location.href='pharmacydataentry.php'">DATA ENTRY</button></div>
            <div><button onclick="window.location.href='pharmacydatamodify.php'">DATA MODIFY</button></div>
            <div><button onclick="window.location.href='pharmacyexceldata.php'">DATA THROUGH EXCEL</button></div>
            <div><button onclick="window.location.href='pharmacydata.php'">INVENTORY</button></div>
        </div>
        <div class="wholecontainer">
            <h1>MEDI-GUIDE</h1>
            <div class="container1">
                <p>Welcome, <b><?php echo $pharmacyName; ?></b>!<br><br>
                ▪️<strong>Effortless Inventory Management</strong> : Easily digitize and organize your entire medicine stock. <br><br>
                ▪️Whenever you want to check whether the medicine is available in your store. <br><br>
                ▪️<strong>Streamlined Operations:</strong> Save time searching shelves and improve efficiency. <br><br>
                ▪️<strong>Quick Stock Tracking: </strong>Quickly check medicine quantities and availability.</p>
            </div>
        </div>
    </div>

    <div class="mobile-menu">
        
        <button class="mobile-menu-button" id="mobile-menu-button">&#9776;</button> </div>
    <div class="mobile-menu-items" id="mobile-menu-items">
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
    </script>
</body>
</html>

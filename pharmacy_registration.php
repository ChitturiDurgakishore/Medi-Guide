<?php
// Database connection details
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

// Function to create user table (modified to include table creation)
function createUserTable($username, $conn, $dbname) {
    // Sanitize the username to prevent SQL injection (important!)
    $safeUsername = preg_replace("/[^a-zA-Z0-9_]/", "", $username); // Remove non-alphanumeric
    if (empty($safeUsername)) {
        return false; // Don't create table with empty or invalid name
    }
    $tableName = $dbname . "." . $safeUsername; // Fully qualify the table name

     // Check if the table already exists
    $checkTableQuery = "SHOW TABLES LIKE '$tableName'";
    $checkTableResult = $conn->query($checkTableQuery);

    if ($checkTableResult->num_rows > 0) {
        return true; // Table exists, no need to create
    }
    // SQL to create table
    $sql = "CREATE TABLE `$tableName` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        medicine_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        expiry_date DATE
    )";

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
        return false; // Indicate failure
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Open+Sans:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f0f4c3;
            color: #333;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            min-height: 100vh;
            box-sizing: border-box;
        }

        .wholepage {
            border: none;
            border-radius: 12px;
            padding-bottom: 20px;
            background-color: transparent;
            box-shadow: none;
            width: 95%;
            max-width: 600px;
            margin-bottom: 20px;
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
            font-size: 2em;
            font-weight: 600;
        }

        h3 {
            background-color: #dcedc8;
            color: #1b5e20;
            width: fit-content;
            border: none;
            border-radius: 8px;
            padding: 8px;
            margin: 10px auto 20px auto;
            text-align: center;
            font-family: 'Open Sans', sans-serif;
            font-size: 1.3em;
            font-weight: 600;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            margin: 5px 0;
            padding: 10px;
            border: 1px solid #81c784;
            border-radius: 6px;
            width: calc(100% - 150px);
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
            font-size: 0.9em;
            transition: border-color 0.3s ease;
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #4caf50;
        }

        .loginbutton, button[type="submit"], button[type="reset"] {
            padding: 10px 20px;
            margin: 10px;
            background-color: #81c784;
            color: white;
            border: 2px solid #81c784;
            border-radius: 30px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-size: 1em;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .loginbutton:hover, button[type="submit"]:hover, button[type="reset"]:hover {
            background-color: #66bb6a;
            transform: scale(1.02);
            border-color: #66bb6a;
        }

        .formpage {
            background-color: #f1f7ed;
            width: 95%;
            max-width: 500px;
            padding: 15px;
            border: 2px solid #81c784;
            border-radius: 12px;
            margin-top: 20px;
            box-sizing: border-box;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .container {
            background-color: transparent;
            padding: 15px;
            border-radius: 12px;
            width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        li {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: wrap;
        }

        ol {
            display: inline-block;
            margin-bottom: 5px;
            color: #388e3c;
            font-family: 'Roboto', sans-serif;
            font-size: 1em;
            font-weight: bold;
            width: 140px;
            text-align: left;
            padding-right: 10px;
            box-sizing: border-box;
        }

        span {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 10px;
        }

        p {
            margin-top: 15px;
            text-align: center;
            color: #555;
            font-family: 'Open Sans', sans-serif;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .wholepage {
                width: 100%;
                border-radius: 0;
                padding: 10px;
            }

            .formpage {
                width: 100%;
                border-radius: 0;
                padding: 10px;
                border: none;
                box-shadow: none;
            }

            input[type="text"],
            input[type="email"],
            input[type="password"] {
                width: calc(100% - 20px);
                display: block;
                margin-left: 0;
            }

            ol {
                width: 100%;
                text-align: left;
                padding-right: 0;
            }

            li {
                flex-direction: column;
                align-items: flex-start;
            }

            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="wholepage">
        <center>
            <div>
                <h1>MEDI-GUIDE</h1>
            </div>
            <div class="formpage">
                <h3>PHARMACY REGISTRATION</h3>
                <div class="container">
                    <form method="post" action="">
                        <ul>
                            <li>
                                <ol>PHARMACY NAME</ol>
                                <input type="text" name="name" required>
                            </li>
                            <li>
                                <ol>PHONE NUMBER</ol>
                                <input type="text" name="phoneno" required>
                            </li>
                            <li>
                                <ol>EMAIL</ol>
                                <input type="email" name="email" required>
                            </li>
                            <li>
                                <ol>PASSWORD</ol>
                                <input type="password" name="password" required>
                            </li>
                            <li>
                                <ol>CONFIRM PASSWORD</ol>
                                <input type="password" name="repassword" required>
                            </li>
                        </ul>
                        <span>
                            <button type="submit" name="submit">SUBMIT</button>
                            <button type="reset" name="clear" onclick="window.location.href='pharmacyregister.php'">CLEAR</button>
                        </span>
                        <?php
                        if (isset($_POST['submit'])) {
                            $username = $_POST['name'];
                            $phoneno = $_POST['phoneno'];
                            $password = $_POST['password'];
                            $email = $_POST['email'];
                            $password_confirm = $_POST['repassword'];

                            if (strcmp($password, $password_confirm) === 0) {
                                 // Call createUserTable function to create the table.
                                if (createUserTable($username, $conn, $dbname)) {
                                    $sql = "INSERT INTO pharmacyregistration (pharmacyname, phoneno, email, password) VALUES (?, ?, ?, ?)";
                                    $ps = $conn->prepare($sql);
                                    $ps->bind_param("ssss", $username, $phoneno, $email, $password);
                                    $ps->execute();
                                     echo "<p style='color:green;'>REGISTRATION SUCCESSFUL! YOU CAN NOW LOGIN</p>";
                                }
                                else{
                                     echo "<p style='color:red;'>Error creating pharmacy table.</p>";
                                }
                            } else {
                                echo "<p style='color:red;'>Password does not match!</p>";
                            }
                        }
                        ?>
                    </form>
                    <div></div>
                    <p>Already have an account?</p>
                    <div>
                        <button onclick="window.location.href='pharmacylogin.php'" class="loginbutton">LOGIN</button>
                    </div>
                </div>
            </div>
        </center>
    </div>
</body>
</html>

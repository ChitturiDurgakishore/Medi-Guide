<?php
session_start();

// Database connection
$servername = "sql113.infinityfree.com";
$username = "if0_37435582";
$password = "RdXGj90Owk";
$dbname = "if0_37435582_mediguide";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$loginMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM pharmacyregistration WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $_SESSION['email'] = $email;
            header("Location: pharmacyhome.php");
            exit();
        } else {
            $loginMessage = "Invalid email or password.";
        }

        $stmt->close();
    } else {
        $loginMessage = "Please enter both email and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Login</title>
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
            background-image: url(bg.jpeg);
            background-size: cover;
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
            border: 2px solid #689f38;
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

        p.error {
            color: red;
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
                <h3>PHARMACY LOGIN</h3>
                <div class="container">
                    <form method="post" action="">
                        <ul>
                            <li>
                                <ol>EMAIL</ol>
                                <input type="email" name="email" required>
                            </li>
                            <li>
                                <ol>PASSWORD</ol>
                                <input type="password" name="password" required>
                            </li>
                        </ul>
                        <span>
                            <button type="submit" name="submit">LOGIN</button>
                            <button type="reset" onclick="window.location.href='pharmacylogin.php'">CLEAR</button>
                        </span>
                        <?php if (!empty($loginMessage)) {
                            echo "<p class='error'>$loginMessage</p>";
                        } ?>
                    </form>
                    <div>
                        <p>Don't have an account?</p>
                    </div>
                    <div>
                        <button onclick="window.location.href='pharmacyregistration.php'" class="loginbutton">REGISTER</button>
                    </div>
                </div>
            </div>
        </center>
    </div>
</body>
</html>

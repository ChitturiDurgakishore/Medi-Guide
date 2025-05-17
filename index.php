<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medi-Guide</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #ff6b6b, #4bcffa); /* Vibrant and contrasting gradient */
            color: #fff;
        }
        .container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 40px; /* Slightly reduced padding */
            margin:20px;
            border-radius: 15px; /* Increased border-radius for a softer look */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
            text-align: center;
            width: 80%; /* Reduced width */
            max-width: 400px; /* Reduced maximum width */
        }
        h1 {
            color: #333;
            margin-bottom: 30px; /* Slightly reduced margin */
            font-size: 2.2em; /* Slightly smaller heading */
        }
        .button-container {
            display: flex;
            flex-direction: column;
            gap: 12px; /* Reduced gap */
            margin-top: 25px; /* Reduced margin-top */
            width: 100%;
        }
        @media (min-width: 600px) {
            .button-container {
                flex-direction: row;
                gap: 15px;
            }
        }
        button {
            padding: 15px 30px; /* Reduced padding */
            font-size: 1em; /* Slightly smaller font size */
            cursor: pointer;
            border: none;
            border-radius: 10px;
            color: #333; /* Dark text for contrast */
            transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            width: 100%;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }
        .customer-button {
            background: linear-gradient(45deg, #ff8a00, #fdd835); /* Energetic orange to yellow */
            color: #333;
        }
        .pharmacy-button {
            background: linear-gradient(45deg, #00c853, #aeea00); /* Vibrant green to lime */
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Medi-Guide</h1>
        <div class="button-container">
            <button class="customer-button" onclick="window.location.href='customer.php'">I am a Customer</button>
            <button class="pharmacy-button" onclick="window.location.href='pharmacyregistration.php'">I am a Pharmacy Owner</button>
        </div>
    </div>
</body>
</html>

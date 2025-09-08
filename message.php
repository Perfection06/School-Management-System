<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Message Type</title>

    <style>
        /* Basic reset and styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f7fc;
        }

        .container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 350px;
        }

        h1 {
            font-size: 26px;
            color: #333;
            margin-bottom: 30px;
        }

        .message-type-options a {
            display: block;
            font-size: 18px;
            color: #fff;
            background-color: #4CAF50;
            padding: 15px;
            margin-bottom: 15px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .message-type-options a:hover {
            background-color: #45a049;
        }

        .message-type-options a:active {
            background-color: #388e3c;
        }

        .message-type-options p {
            font-size: 16px;
            margin: 20px 0 0;
            color: #777;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="container">
        <h1>Select Message Type</h1>
        
        <div class="message-type-options">
            <a href="./broadcast.php">Broadcast Message</a>
            <a href="./individual.php">Individual Message</a>
        </div>

        <p>Please choose the type of message you'd like to send. You can either send a message to a group (broadcast) or to individual users.</p>
    </div>
</body>
</html>

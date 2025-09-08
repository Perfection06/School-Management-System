<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background: linear-gradient(90deg,rgb(71, 127, 195),rgb(33, 77, 160));
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .nav-links {
            display: flex;
            gap: 15px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            transition: background 0.3s, transform 0.2s;
        }

        .nav-links a:hover {
            background: orange;
            transform: scale(1.1);
        }

        .profile-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-container span {
            color: white;
            font-size: 16px;
            font-weight: bold;
        }

        .profile-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid white;
            transition: transform 0.3s;
        }

        .profile-icon:hover {
            transform: scale(1.1);
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="nav-links">
            <a href="./Cashier_Dashboard.php">Fee Payment</a>
            <a href="other_payment.php">Other Payment</a>
            <a href="../login.php">Logout</a>
        </div>
        <div class="profile-container">
            <a href="profile.php">
                <img src="../Resources/user.png" alt="Profile" class="profile-icon">
            </a>
        </div>
    </div>
</body>
</html>

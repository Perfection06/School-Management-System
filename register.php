<?php
session_start();

// Connect to the database
include('db_connection.php');

// Initialize alert message
$alertMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Validate form data
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $alertMessage = "All fields are required!";
    } elseif ($password !== $confirmPassword) {
        $alertMessage = "Passwords do not match!";
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert into the database
        $sql = "INSERT INTO admin (username, password) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([$username, $hashedPassword]);
            $alertMessage = "Registration successful!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry error
                $alertMessage = "Username is already taken!";
            } else {
                $alertMessage = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f4f6f8;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            margin-bottom: 20px;
            color: #3498db;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-weight: bold;
            color: #333;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        .alert {
            padding: 10px;
            margin-top: 10px;
            color: white;
            background-color: #e74c3c;
            border-radius: 5px;
        }
        .alert.success {
            background-color: #2ecc71;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Register Admin</h2>
    <?php if ($alertMessage): ?>
        <div class="alert <?php echo strpos($alertMessage, 'successful') !== false ? 'success' : ''; ?>">
            <?php echo htmlspecialchars($alertMessage); ?>
        </div>
    <?php endif; ?>
    <form method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit">Register</button>
    </form>
</div>
</body>
</html>

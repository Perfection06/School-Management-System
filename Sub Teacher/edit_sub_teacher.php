<?php
session_start();
include("db_connection.php");

// Check if the user is logged in
if (!isset($_SESSION['user']['username'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['user']['username'];
$errors = [];
$success = "";

// Fetch user details
$sql = "SELECT profile_image FROM noclass_teacher WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$user_details = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission for profile image update
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../uploads/";
    $file_name = uniqid() . "_" . basename($_FILES["profile_image"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
        $sql_update_image = "UPDATE noclass_teacher SET profile_image = ? WHERE username = ?";
        $stmt = $pdo->prepare($sql_update_image);
        $stmt->execute([$file_name, $username]);

        echo "<script>
                alert('Profile image updated successfully!');
                window.location.href = 'profile.php';
              </script>";
        exit;
    } else {
        $errors[] = "Failed to upload the image.";
    }
}

// Handle form submission for password update
if (!empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_update_password = "UPDATE user SET password = ? WHERE username = ?";
        $stmt = $pdo->prepare($sql_update_password);
        $stmt->execute([$hashed_password, $username]);

        echo "<script>
                alert('Password updated successfully!');
                window.location.href = 'profile.php';
              </script>";
        exit;
    } else {
        $errors[] = "Passwords do not match.";
    }
}

$pdo = null; // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <style>
        /* Reset default styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f7fc;
    color: #333;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    background-color: #fff;
    width: 100%;
    max-width: 600px;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    text-align: center;
}

h1 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #2c3e50;
}

.form-group {
    margin-bottom: 20px;
    text-align: left;
}

label {
    font-weight: bold;
    margin-bottom: 5px;
    display: block;
    color: #34495e;
}

.input-field, .input-file {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    color: #34495e;
}

.input-file {
    padding: 5px;
}

.profile-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin-bottom: 10px;
}

.submit-btn {
    width: 100%;
    padding: 12px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.submit-btn:hover {
    background-color: #2980b9;
}

.errors {
    list-style-type: none;
    padding: 0;
    margin-bottom: 20px;
    color: #e74c3c;
}

.errors li {
    background-color: #f8d7da;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 5px;
}

.success {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
    font-weight: bold;
}

.form-footer {
    margin-top: 20px;
}

.back-link {
    color: #3498db;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #2980b9;
}

    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Profile</h1>

        <!-- Success message -->
        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <!-- Error messages -->
        <?php if ($errors): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form action="#" method="POST" enctype="multipart/form-data">
            <!-- Profile Image -->
            <div class="form-group">
                <label for="profile_image">Profile Image</label>
                <?php if (!empty($user_details['profile_image'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($user_details['profile_image']) ?>" alt="Profile Image" class="profile-image">
                <?php endif; ?>
                <input type="file" name="profile_image" id="profile_image" class="input-file">
            </div>

            <!-- New Password -->
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" class="input-field">
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="input-field">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Save Changes</button>
        </form>

        <div class="form-footer">
            <a href="profile.php" class="back-link">Back to Profile</a>
        </div>
    </div>
</body>
</html>

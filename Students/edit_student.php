<?php
session_start();
include("db_connection.php");

// Check if the user is logged in and is a Student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['user']['username'];
$errors = [];
$success = "";

// Fetch student details
$sql = "SELECT sa.student_image FROM Student_Admissions sa 
        INNER JOIN Students s ON sa.id = s.id WHERE s.username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$student_details = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle case where no data is found
if (!$student_details) {
    $student_details = ['student_image' => null]; // Default value
}

// Handle profile image upload
if (isset($_FILES['student_image']) && $_FILES['student_image']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "../uploads/";
    $original_name = pathinfo($_FILES["student_image"]["name"], PATHINFO_FILENAME);
    $extension = strtolower(pathinfo($_FILES["student_image"]["name"], PATHINFO_EXTENSION));
    $file_name = "uploads/" . $original_name . "." . $extension;
    $target_file = $target_dir . $original_name . "." . $extension;

    // Validate file type (only images)
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($extension, $allowed_types)) {
        $errors[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
    } else {
        // Move the uploaded file to the server
        if (move_uploaded_file($_FILES["student_image"]["tmp_name"], $target_file)) {
            // Save the file name in the database
            $sql_update_image = "UPDATE Student_Admissions sa
                                INNER JOIN Students s ON sa.id = s.id
                                SET sa.student_image = ? WHERE s.username = ?";
            $stmt = $pdo->prepare($sql_update_image);
            $stmt->execute([$file_name, $username]);

            if ($upload_success) {
                
                echo "<script>alert('Failed to upload the image.'); window.location.href='profile.php';</script>";
                
                $student_details['student_image'] = $file_name;
            } else {
                echo "<script>alert('Profile image updated successfully!'); window.location.href='profile.php';</script>";
                $errors[] = "Failed to upload the image.";
            }
        }
    }
}

// Update Password
if (!empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_update_password = "UPDATE Students SET password = ? WHERE username = ?";
        $stmt = $pdo->prepare($sql_update_password);
        $stmt->execute([$hashed_password, $username]);

        
        if ($password1 === $password2) {
            // Assuming password update logic is here
            echo "<script>alert('Password updated successfully!'); window.location.href='profile.php';</script>";
        } else {
            echo "<script>alert('Passwords do not match.'); window.location.href='profile.php';</script>";
        }
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
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
        }

        .form-container {
            width: 40%;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            font-size: 16px;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }

        .form-group input[type="password"],
        .form-group input[type="file"],
        .form-group button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-group input[type="password"],
        .form-group input[type="file"] {
            background-color: #f9f9f9;
            color: #333;
            transition: background-color 0.3s ease;
        }

        .form-group input[type="password"]:focus,
        .form-group input[type="file"]:focus {
            background-color: #fff;
            border-color: #007bff;
            outline: none;
        }

        .form-group img.profile-image {
            max-width: 150px;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 50%;
            object-fit: cover;
        }

        .btn {
            background-color: #007bff;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .input-file {
            margin-top: 10px;
        }

        .form-container img {
            display: block;
            margin: 10px auto;
        }

        .form-group input[type="file"] {
            background-color: transparent;
            border: 1px solid #007bff;
            padding: 10px;
        }

        .form-group input[type="file"]::-webkit-file-upload-button {
            cursor: pointer;
            background-color: #007bff;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            border: none;
        }

        .form-group input[type="file"]::-webkit-file-upload-button:hover {
            background-color: #0056b3;
        }

        .preview-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .preview-container img {
            display: block;
            max-width: 100px;
            max-height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }

        .form-group input[type="file"]:focus ~ .preview-container img {
            display: block;
        }

        @media (max-width: 768px) {
            .form-container {
                width: 80%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2>Edit Profile</h2>
        <form action="edit_student.php" method="POST" enctype="multipart/form-data">
            <!-- Profile Image -->
            <div class="form-group">
                <label for="student_image">Profile Image</label>
                <div>
                    <?php if (!empty($student_details['student_image'])): ?>
                        <img id="current_image" src="../<?= htmlspecialchars($student_details['student_image']) ?>" alt="Profile Image" class="profile-image">
                    <?php else: ?>
                        <img id="current_image" src="../Resources/default_profile.png" alt="No Image" class="profile-image">
                    <?php endif; ?>
                </div>
                <input type="file" name="student_image" id="student_image" class="input-file" onchange="previewImage(event)">
                <div class="preview-container">
                    <img id="preview_image" class="profile-image" style="display:none;">
                </div>
            </div>

            <!-- Password Update -->
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control">
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(event) {
        const preview = document.getElementById('preview_image');
        const file = event.target.files[0];

        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }

</script>
</html>



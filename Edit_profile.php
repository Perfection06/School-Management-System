<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include ("database_connection.php");

// Initialize variables
$user_id = isset($_GET['username']) ? $_GET['username'] : '';
$errors = [];
$success = '';
$user_details = null; // Initialize to avoid undefined variable warning
$is_teacher = false;

// Function to sanitize input data
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Fetch list of grades for the dropdown
$grades = [];
$grades_sql = "SELECT id, grade_name FROM grades";
if ($stmt = $conn->prepare($grades_sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    $stmt->close();
} else {
    $errors[] = "Failed to fetch grades: " . $conn->error;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $user_id = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
    $full_name = isset($_POST['full_name']) ? sanitize_input($_POST['full_name']) : '';
    $whatsapp_number = isset($_POST['whatsapp_number']) ? sanitize_input($_POST['whatsapp_number']) : '';
    $residence_number = isset($_POST['residence_number']) ? sanitize_input($_POST['residence_number']) : '';
    $postal_address = isset($_POST['postal_address']) ? sanitize_input($_POST['postal_address']) : '';
    $marital_status = isset($_POST['marital_status']) ? sanitize_input($_POST['marital_status']) : '';
    $assigned_class_id = isset($_POST['assigned_class_id']) ? sanitize_input($_POST['assigned_class_id']) : '';

    // Validate required fields
    if (empty($full_name)) {
        $errors[] = "Full Name is required.";
    }
    if (empty($whatsapp_number)) {
        $errors[] = "WhatsApp Number is required.";
    }
    if (empty($residence_number)) {
        $errors[] = "Residence Number is required.";
    }
    if (empty($postal_address)) {
        $errors[] = "Address is required.";
    }
    if (empty($marital_status)) {
        $errors[] = "Marital Status is required.";
    }

    // Handle Profile Image Upload
    $profile_image_path = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        $file_tmp = $_FILES['profile_image']['tmp_name'];
        $file_name = basename($_FILES['profile_image']['name']);
        $file_size = $_FILES['profile_image']['size'];
        $file_type = mime_content_type($file_tmp);

        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF files are allowed for the profile image.";
        }

        // Validate file size
        if ($file_size > $max_size) {
            $errors[] = "Profile image size should not exceed 2MB.";
        }

        // If no errors, proceed to upload
        if (empty($errors)) {
            $upload_dir = "./uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Generate a unique file name to prevent overwriting
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid('profile_', true) . '.' . $file_extension;
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                $profile_image_path = $destination;

                // Optionally, delete the old profile image if it exists and is not the default image
                // Fetch current profile image
                $current_image_sql = "SELECT users.profile_image FROM users WHERE users.user_id = ?";
                if ($stmt = $conn->prepare($current_image_sql)) {
                    $stmt->bind_param("s", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $current_image = $result->fetch_assoc()['profile_image'];
                        if ($current_image && file_exists($current_image) && $current_image !== './Resources/default_profile.png') {
                            unlink($current_image); // Delete the old image
                        }
                    }
                    $stmt->close();
                }
            } else {
                $errors[] = "Failed to upload the profile image.";
            }
        }
    }

    // Proceed only if there are no validation or upload errors
    if (empty($errors)) {
        // **START: Uniqueness Check for WhatsApp and Residence Numbers**
        // This section has been removed as per your request.
        // **END: Uniqueness Check**

        // **Proceed with Update Without Uniqueness Check**

        // Determine if the user is a Teacher or Staff
        $is_teacher = false;
        $teacher_id = null;
        $staff_id = null;

        // Check if the user is a Teacher
        $teacher_check_sql = "SELECT teacher_id FROM teachers WHERE username = ?";
        if ($stmt = $conn->prepare($teacher_check_sql)) {
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $is_teacher = true;
                $teacher_id = $result->fetch_assoc()['teacher_id'];
            }
            $stmt->close();
        }

        if ($is_teacher) {
            // Update Teachers Table
            $update_teacher_sql = "UPDATE teachers SET 
                                    full_name = ?, 
                                    whatsapp_number = ?, 
                                    residence_number = ?, 
                                    postal_address = ?, 
                                    marital_status = ?, 
                                    assigned_class_id = ?
                                    WHERE username = ?";
            if ($stmt = $conn->prepare($update_teacher_sql)) {
                $stmt->bind_param("sssssss", $full_name, $whatsapp_number, $residence_number, $postal_address, $marital_status, $assigned_class_id, $user_id);
                if ($stmt->execute()) {
                    // Update profile image in users table if a new image was uploaded
                    if (!empty($profile_image_path)) {
                        $update_image_sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
                        if ($image_stmt = $conn->prepare($update_image_sql)) {
                            $image_stmt->bind_param("ss", $profile_image_path, $user_id);
                            if (!$image_stmt->execute()) {
                                $errors[] = "Failed to update profile image: " . $image_stmt->error;
                            }
                            $image_stmt->close();
                        } else {
                            $errors[] = "Failed to prepare profile image update: " . $conn->error;
                        }
                    }
                    $success = "Profile updated successfully.";
                } else {
                    $errors[] = "Failed to update profile: " . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            // Check if the user is Staff
            $staff_check_sql = "SELECT staff_id FROM staff WHERE username = ?";
            if ($stmt = $conn->prepare($staff_check_sql)) {
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $staff_id = $result->fetch_assoc()['staff_id'];
                }
                $stmt->close();
            }

            if ($staff_id) {
                // Update Staff Table
                $update_staff_sql = "UPDATE staff SET 
                                     full_name = ?, 
                                     whatsapp_number = ?, 
                                     residence_number = ?, 
                                     postal_address = ?, 
                                     marital_status = ?
                                     WHERE username = ?";
                if ($stmt = $conn->prepare($update_staff_sql)) {
                    $stmt->bind_param("ssssss", $full_name, $whatsapp_number, $residence_number, $postal_address, $marital_status, $user_id);
                    if ($stmt->execute()) {
                        // Update profile image in users table if a new image was uploaded
                        if (!empty($profile_image_path)) {
                            $update_image_sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
                            if ($image_stmt = $conn->prepare($update_image_sql)) {
                                $image_stmt->bind_param("ss", $profile_image_path, $user_id);
                                if (!$image_stmt->execute()) {
                                    $errors[] = "Failed to update profile image: " . $image_stmt->error;
                                }
                                $image_stmt->close();
                            } else {
                                $errors[] = "Failed to prepare profile image update: " . $conn->error;
                            }
                        }
                        $success = "Profile updated successfully.";
                    } else {
                        $errors[] = "Failed to update profile: " . $stmt->error;
                    }
                    $stmt->close();
                }
            } else {
                $errors[] = "User not found.";
            }
        }
    }
}

    // Fetch User Details for Form Display
    if ($user_id) {
        // First, check if the user is a teacher
        $teacher_sql = "
            SELECT 
                teachers.teacher_id,
                teachers.full_name,
                teachers.whatsapp_number,
                teachers.residence_number,
                teachers.postal_address,
                teachers.marital_status,
                teachers.assigned_class_id,
                users.profile_image,
                grades.grade_name
            FROM 
                teachers
            LEFT JOIN 
                users ON teachers.username = users.user_id
            LEFT JOIN 
                grades ON teachers.assigned_class_id = grades.id
            WHERE 
                teachers.username = ?";

        if ($stmt = $conn->prepare($teacher_sql)) {
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user_details = $result->fetch_assoc();
                $is_teacher = true;
            }
            $stmt->close();
        }

        // If not a teacher, check if the user is staff
        if (!$user_details) {
            $staff_sql = "
                SELECT 
                    staff.staff_id,
                    staff.full_name,
                    staff.whatsapp_number,
                    staff.residence_number,
                    staff.postal_address,
                    staff.marital_status,
                    users.profile_image
                FROM 
                    staff
                LEFT JOIN 
                    users ON staff.username = users.user_id
                WHERE 
                    staff.username = ?";

            if ($stmt = $conn->prepare($staff_sql)) {
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $user_details = $result->fetch_assoc();
                    $is_teacher = false;
                }
                $stmt->close();
            }
        }
    }

    // Closing the connection
    $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - <?php echo htmlspecialchars($user_details['full_name'] ?? ''); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #eef2f3;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 700px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-image {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #3498db;
            margin-bottom: 15px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }
        label {
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        input[type="text"],
        textarea,
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        textarea {
            resize: vertical;
            height: 80px;
        }
        .form-group.file-group {
            flex-direction: column;
        }
        .form-group.file-group label {
            margin-bottom: 5px;
        }
        .form-group.file-group input[type="file"] {
            padding: 0;
        }
        .buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .buttons button,
        .buttons a {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
            transition: background-color 0.3s ease;
        }
        .buttons button.submit-btn {
            background-color: #3498db;
        }
        .buttons button.submit-btn:hover {
            background-color: #2980b9;
        }
        .buttons a.back-btn {
            background-color: #95a5a6;
        }
        .buttons a.back-btn:hover {
            background-color: #7f8c8d;
        }
        .error {
            background-color: #e74c3c;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .success {
            background-color: #2ecc71;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .no-data {
            text-align: center;
            color: #e74c3c;
            font-size: 18px;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #3498db;
            text-decoration: none;
            font-size: 16px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Profile</h2>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if ($user_details): ?>
            <div class="profile-header">
                <?php if (!empty($user_details['profile_image']) && file_exists($user_details['profile_image'])): ?>
                    <img class="profile-image" src="<?php echo htmlspecialchars($user_details['profile_image']); ?>" alt="Profile Image">
                <?php else: ?>
                    <img class="profile-image" src="./Resources/default_profile.png" alt="No Image">
                <?php endif; ?>
            </div>
            <form action="Edit_profile.php?username=<?php echo urlencode($user_id); ?>" method="POST" enctype="multipart/form-data">
                <!-- Hidden Field to Pass User ID -->
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user_id); ?>">

                <div class="form-group">
                    <label for="full_name">Full Name:</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_details['full_name'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="whatsapp_number">WhatsApp Number:</label>
                    <input type="text" id="whatsapp_number" name="whatsapp_number" value="<?php echo htmlspecialchars($user_details['whatsapp_number'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="residence_number">Residence Number:</label>
                    <input type="text" id="residence_number" name="residence_number" value="<?php echo htmlspecialchars($user_details['residence_number'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="postal_address">Address:</label>
                    <textarea id="postal_address" name="postal_address" required><?php echo htmlspecialchars($user_details['postal_address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="marital_status">Marital Status:</label>
                    <select id="marital_status" name="marital_status" required>
                        <option value="">--Select--</option>
                        <option value="Single" <?php echo (isset($user_details['marital_status']) && $user_details['marital_status'] === 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo (isset($user_details['marital_status']) && $user_details['marital_status'] === 'Married') ? 'selected' : ''; ?>>Married</option>
                        <option value="Other" <?php echo (isset($user_details['marital_status']) && $user_details['marital_status'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>

                <?php if ($is_teacher): ?>
                    <div class="form-group">
                        <label for="assigned_class_id">Assigned Class:</label>
                        <select id="assigned_class_id" name="assigned_class_id" required>
                            <option value="">--Select Class--</option>
                            <?php foreach ($grades as $grade): ?>
                                <option value="<?php echo htmlspecialchars($grade['id']); ?>" <?php echo (isset($user_details['assigned_class_id']) && $user_details['assigned_class_id'] == $grade['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($grade['grade_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group file-group">
                    <label for="profile_image">Profile Image:</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*">
                </div>

                <div class="buttons">
                    <button type="submit" class="submit-btn">Save Changes</button>
                    <a href="./profile.php?username=<?php echo urlencode($user_id); ?>" class="back-btn">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <p class="no-data">User not found.</p>
            <div class="back-link">
                <a href="./view_teachers_staff.php">&larr; Back to Teachers and Staff List</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
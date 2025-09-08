<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'database_connection.php';

// Get the username from the query parameter
$username_filter = isset($_GET['username']) ? $_GET['username'] : '';

// Fetch staff details
$query = "
    SELECT staff.*, user.role, educational_details.other_educational_qualification, 
           educational_details.professional_qualification, educational_details.extra_curricular_activities, 
           educational_details.work_experience
    FROM staff 
    LEFT JOIN user ON staff.username = user.username
    LEFT JOIN educational_details ON staff.username = educational_details.username
    WHERE staff.username = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username_filter);
$stmt->execute();
$user_details = $stmt->get_result()->fetch_assoc();

// Fetch previous job roles
$previous_roles_query = "SELECT * FROM previous_info WHERE username = ?";
$roles_stmt = $conn->prepare($previous_roles_query);
$roles_stmt->bind_param("s", $username_filter);
$roles_stmt->execute();
$previous_roles = $roles_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $full_name = $_POST['full_name'];
    $gender = $_POST['gender'];
    $dob = $_POST['date_of_birth'];
    $postal_address = $_POST['postal_address'];
    $ethnicity = $_POST['ethnicity'];
    $nic = $_POST['nic_number'];
    $marital_status = $_POST['marital_status'];
    $whatsapp = $_POST['whatsapp_number'];
    $residence = $_POST['residence_number'];
    $first_language = $_POST['first_language'];
    $position = $_POST['position'];
    $other_qualification = $_POST['other_qualification'];
    $professional_qualification = $_POST['professional_qualification'];
    $activities = $_POST['activities'];
    $work_experience = $_POST['work_experience'];

    // Handle profile image upload
$profile_image = null;

// Check if the file is uploaded and there are no errors
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    // Set target directory
    $target_dir = "uploads/";

    // Ensure the target directory exists; create if it doesn't
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Get the original file name
    $profile_image = basename($_FILES['profile_image']['name']);
    $target_path = $target_dir . $profile_image;

    // Optional: Validate the file type (e.g., jpg, jpeg, png, gif)
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
        echo "<script>alert('Invalid file type. Only JPG, PNG, and GIF are allowed.');</script>";
        $profile_image = $user_details['profile_image']; // Keep the old image if invalid type
    } else {
        // Delete old profile image if it exists
        if (!empty($user_details['profile_image']) && file_exists($target_dir . $user_details['profile_image'])) {
            unlink($target_dir . $user_details['profile_image']);
        }

        // Upload the new profile image
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
            echo "<script>alert('Failed to upload the profile image.');</script>";
            $profile_image = $user_details['profile_image']; // Keep the old image if upload fails
        } else {
            echo "Profile image uploaded successfully";
        }
    }
} else {
    // If no file is uploaded, retain the existing image
    $profile_image = $user_details['profile_image'];
}


    // Update staff details
    $update_staff_query = "
        UPDATE staff 
        SET full_name = ?, gender = ?, date_of_birth = ?, postal_address = ?, ethnicity = ?, 
            nic_number = ?, marital_status = ?, whatsapp_number = ?, residence_number = ?, 
            first_language = ?, position = ?, profile_image = ?
        WHERE username = ?";
    $staff_stmt = $conn->prepare($update_staff_query);
    $staff_stmt->bind_param(
        "sssssssssssss",
        $full_name,
        $gender,
        $dob,
        $postal_address,
        $ethnicity,
        $nic,
        $marital_status,
        $whatsapp,
        $residence,
        $first_language,
        $position,
        $profile_image,
        $username_filter
    );

    // Update educational details
    $update_edu_query = "
        UPDATE educational_details 
        SET other_educational_qualification = ?, professional_qualification = ?, 
            extra_curricular_activities = ?, work_experience = ?
        WHERE username = ?";
    $edu_stmt = $conn->prepare($update_edu_query);
    $edu_stmt->bind_param(
        "sssss",
        $other_qualification,
        $professional_qualification,
        $activities,
        $work_experience,
        $username_filter
    );

    // Handle previous job roles
    $delete_roles_query = "DELETE FROM previous_info WHERE username = ?";
    $delete_roles_stmt = $conn->prepare($delete_roles_query);
    $delete_roles_stmt->bind_param("s", $username_filter);
    $delete_roles_stmt->execute();

    // Insert new job roles
    $previous_roles = $_POST['previous_role'];
    $previous_companies = $_POST['previous_company'];
    $years_experiences = $_POST['years_experience'];

    $insert_role_query = "INSERT INTO previous_info (username, previous_role, previous_company, years_experience) VALUES (?, ?, ?, ?)";
    $role_stmt = $conn->prepare($insert_role_query);

    foreach ($previous_roles as $index => $role) {
        $company = $previous_companies[$index];
        $years = $years_experiences[$index];
        $role_stmt->bind_param("sssi", $username_filter, $role, $company, $years);
        $role_stmt->execute();
    }

    if ($staff_stmt->execute() && $edu_stmt->execute()) {
        echo "
            <script>
                alert('Profile updated successfully.');
                window.location.href = 'staff_profile.php?username=" . urlencode($username_filter) . "';
            </script>
        ";
        exit; // Ensure no further code is executed
    } else {
        echo "
            <script>
                alert('Error updating profile. Please try again.');
                window.history.back(); // Redirect back to the form if there's an error
            </script>
        ";
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff Profile</title>
    <style>
        /* General Styling */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
            color: #333;
        }

        h1 {
            text-align: center;
            margin-top: 40px;
            font-size: 28px;
            color: #333;
        }

        form {
            max-width: 900px;
            margin: 30px auto;
            padding: 40px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        h3 {
            color: #4CAF50;
            font-size: 22px;
            margin-bottom: 20px;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 8px;
        }

        label {
            display: block;
            margin: 8px 0 4px;
            font-size: 14px;
            color: #555;
        }

        input[type="text"],
        input[type="date"],
        input[type="number"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 14px;
            margin: 8px 0 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border 0.3s ease;
        }

        input[type="file"] {
            padding: 6px;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            border-color: #4CAF50;
            outline: none;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 14px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }

        button:hover {
            background-color: #45a049;
        }

        .previous_role_entry {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        .previous_role_entry input {
            width: 48%;
            display: inline-block;
            margin-right: 4%;
        }

        .previous_role_entry input:last-child {
            margin-right: 0;
        }

        .previous_role_entry button {
            display: inline-block;
            background-color: #f44336;
            padding: 10px 15px;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .previous_role_entry button:hover {
            background-color: #e53935;
        }

        #previous_roles_container {
            display: flex;
            flex-direction: column;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .profile-image-container {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            justify-content: center;
        }

        .profile-image-container img {
            margin-right: 20px;
            border: 3px solid #4CAF50;
        }

        .add-more-btn {
            background-color: #2196F3;
            color: white;
            padding: 12px 18px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .add-more-btn:hover {
            background-color: #1976D2;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
        }

        .back-link a {
            color: #2196F3;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #1976D2;
        }

    </style>
</head>
<body>

    <h1>Edit Staff Profile</h1>
    <form method="POST" enctype="multipart/form-data">

        <div class="form-section profile-image-container">
            <img src="uploads/<?php echo htmlspecialchars($user_details['profile_image']); ?>" alt="Profile Image" width="150">
            <div>
                <h3>Profile Image</h3>
                <label for="profile_image">Choose a new profile image:</label>
                <input type="file" name="profile_image" id="profile_image" accept="image/*">
            </div>
        </div>

        <div class="form-section">
            <h3>Personal Information</h3>
            <label>Full Name:</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_details['full_name']); ?>" required><br>

            <label>Gender:</label>
            <select name="gender" required>
                <option value="Male" <?php echo $user_details['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo $user_details['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
            </select><br>

            <label>Date of Birth:</label>
            <input type="date" name="date_of_birth" value="<?php echo $user_details['date_of_birth']; ?>" required><br>

            <label>Postal Address:</label>
            <textarea name="postal_address" required><?php echo htmlspecialchars($user_details['postal_address']); ?></textarea><br>

            <label>Ethnicity:</label>
            <input type="text" name="ethnicity" value="<?php echo htmlspecialchars($user_details['ethnicity']); ?>"><br>

            <label>NIC Number:</label>
            <input type="text" name="nic_number" value="<?php echo htmlspecialchars($user_details['nic_number']); ?>" required><br>

            <label>Marital Status:</label>
            <select name="marital_status" required>
                <option value="Single" <?php echo $user_details['marital_status'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                <option value="Married" <?php echo $user_details['marital_status'] === 'Married' ? 'selected' : ''; ?>>Married</option>
            </select><br>

            <label>WhatsApp Number:</label>
            <input type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($user_details['whatsapp_number']); ?>"><br>

            <label>Residence Number:</label>
            <input type="text" name="residence_number" value="<?php echo htmlspecialchars($user_details['residence_number']); ?>"><br>

            <label>First Language:</label>
            <input type="text" name="first_language" value="<?php echo htmlspecialchars($user_details['first_language']); ?>"><br>

            <label>Position:</label>
            <input type="text" name="position" value="<?php echo htmlspecialchars($user_details['position']); ?>"><br>
        </div>

        <div class="form-section">
            <h3>Educational Details</h3>
            <label>Other Educational Qualification:</label>
            <textarea name="other_qualification"><?php echo htmlspecialchars($user_details['other_educational_qualification']); ?></textarea><br>

            <label>Professional Qualification:</label>
            <textarea name="professional_qualification"><?php echo htmlspecialchars($user_details['professional_qualification']); ?></textarea><br>

            <label>Extra-Curricular Activities:</label>
            <textarea name="activities"><?php echo htmlspecialchars($user_details['extra_curricular_activities']); ?></textarea><br>

            <label>Work Experience:</label>
            <textarea name="work_experience"><?php echo htmlspecialchars($user_details['work_experience']); ?></textarea><br>
        </div>

        <div class="form-section">
            <h3>Previous Roles and Job History</h3>
            <fieldset>
                <legend>Past Roles and Job History</legend>
                <div id="previous_roles_container">
                    <?php foreach ($previous_roles as $role) { ?>
                        <div class="previous_role_entry">
                            <label for="previous_role">Previous Role:</label>
                            <input type="text" name="previous_role[]" value="<?php echo htmlspecialchars($role['previous_role']); ?>" required>

                            <label for="previous_company">Previous Company/Organization:</label>
                            <input type="text" name="previous_company[]" value="<?php echo htmlspecialchars($role['previous_company']); ?>" required>

                            <label for="years_experience">Total Years of Experience:</label>
                            <input type="number" name="years_experience[]" value="<?php echo htmlspecialchars($role['years_experience']); ?>" required>

                            <button type="button" onclick="removeRole(this)">Remove</button>
                        </div>
                    <?php } ?>
                </div>
                <button type="button" class="add-more-btn" onclick="addMoreRoles()">Add More</button>
            </fieldset>
        </div>

        <button type="submit">Update Profile</button>

    </form>

    <!-- Back link -->
    <div class="back-link">
        <a href="staff_profile.php?username=<?php echo urlencode($username_filter); ?>">Back to Profile</a>
    </div>


    <script>
        function addMoreRoles() {
            const container = document.getElementById('previous_roles_container');
            const newEntry = document.createElement('div');
            newEntry.classList.add('previous_role_entry');
            newEntry.innerHTML = `
                <label for="previous_role">Previous Role:</label>
                <input type="text" name="previous_role[]" placeholder="e.g., Teacher, Administrator" required>

                <label for="previous_company">Previous Company/Organization:</label>
                <input type="text" name="previous_company[]" placeholder="e.g., ABC School, XYZ Company" required>

                <label for="years_experience">Total Years of Experience:</label>
                <input type="number" name="years_experience[]" placeholder="e.g., 5" required>

                <button type="button" onclick="removeRole(this)">Remove</button>
            `;
            container.appendChild(newEntry);
        }

        function removeRole(button) {
            button.parentElement.remove();
        }
    </script>

</body>
</html>

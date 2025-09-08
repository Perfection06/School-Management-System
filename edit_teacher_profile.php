<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'database_connection.php';

// Fetch username from query parameter
$username_filter = isset($_GET['username']) ? $_GET['username'] : '';

// Fetch teacher details, including teaching_classes as grade IDs
$sql_teacher = "
    SELECT 
        t.*, 
        s.subject_name, 
        ed.other_educational_qualification, 
        ed.professional_qualification, 
        ed.extra_curricular_activities, 
        ed.work_experience 
    FROM teacher t
    LEFT JOIN subjects s ON t.subject_id = s.id
    LEFT JOIN educational_details ed ON t.username = ed.username
    WHERE t.username = ?";
$stmt = $conn->prepare($sql_teacher);
$stmt->bind_param("s", $username_filter);
$stmt->execute();
$teacher_details = $stmt->get_result()->fetch_assoc();

// Decode teaching_classes JSON to get grade IDs
$current_classes = [];
if (!empty($teacher_details['teaching_classes'])) {
    $current_classes = json_decode($teacher_details['teaching_classes'], true);
}

// Fetch all grades
$sql_all_grades = "SELECT id, grade_name FROM grades";
$result_all_grades = $conn->query($sql_all_grades);
$grades = $result_all_grades->fetch_all(MYSQLI_ASSOC);

// Fetch all subjects
$sql_subjects = "SELECT id, subject_name FROM subjects";
$subjects_result = $conn->query($sql_subjects);
$subjects = $subjects_result->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and fetch POST data
    $full_name = $_POST['full_name'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['date_of_birth'] ?? '';
    $address = $_POST['postal_address'] ?? '';
    $nic = $_POST['nic_number'] ?? '';
    $marital_status = $_POST['marital_status'] ?? '';
    $whatsapp = $_POST['whatsapp_number'] ?? '';
    $residence = $_POST['residence_number'] ?? '';
    $language = $_POST['first_language'] ?? '';
    $grade_id = $_POST['class_teacher_grade'] ?? null;
    $subject_id = $_POST['subject'] ?? null;
    $rank = $_POST['rank'] ?? null;

    // Handle file upload for profile image
    $profile_image = $teacher_details['profile_image']; // Keep the old image by default

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        $file_name = uniqid() . "_" . basename($_FILES['profile_image']['name']);
        $target_file = $upload_dir . $file_name;

        // Ensure upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Create directory if it doesn't exist
        }

        // Move the uploaded file
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Successfully uploaded; update the profile image path
            $profile_image = 'uploads/' . $file_name;
        } else {
            echo "<script>alert('Failed to upload profile image.');</script>";
        }
    }

    // Update teacher details in the database, including profile_image
    $update_teacher = "
        UPDATE teacher 
        SET 
            full_name = ?, 
            gender = ?, 
            date_of_birth = ?, 
            postal_address = ?, 
            nic_number = ?, 
            marital_status = ?, 
            whatsapp_number = ?, 
            residence_number = ?, 
            first_language = ?, 
            grade_id = ?, 
            subject_id = ?, 
            profile_image = ?,
            teaching_classes = ?,
            rank = ?
        WHERE username = ?";
    $selected_grades = $_POST['grades'] ?? [];
    $teaching_classes_json = json_encode($selected_grades);

    $stmt = $conn->prepare($update_teacher);
    $stmt->bind_param(
        "sssssssssiissss",
        $full_name,
        $gender,
        $dob,
        $address,
        $nic,
        $marital_status,
        $whatsapp,
        $residence,
        $language,
        $grade_id,
        $subject_id,
        $profile_image,
        $teaching_classes_json,
        $rank,
        $username_filter
    );

    $stmt->execute();

    // Update educational details
    $other_qualifications = $_POST['other_qualifications'] ?? '';
    $professional_qualifications = $_POST['professional_qualifications'] ?? '';
    $activities = $_POST['activities'] ?? '';
    $experience = $_POST['experience'] ?? '';

    $update_education = "
        UPDATE educational_details 
        SET other_educational_qualification = ?, professional_qualification = ?, 
            extra_curricular_activities = ?, work_experience = ?
        WHERE username = ?";
    $stmt_edu = $conn->prepare($update_education);
    $stmt_edu->bind_param(
        "sssss",
        $other_qualifications,
        $professional_qualifications,
        $activities,
        $experience,
        $username_filter
    );
    $stmt_edu->execute();

    // Redirect with success message
    echo "
        <script>
            alert('Teacher profile updated successfully.');
            window.location.href = 'teacher_profile.php?username=" . urlencode($username_filter) . "';
        </script>
    ";
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        /* Animations */
        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.8) rotate(-2deg); }
            60% { opacity: 1; transform: scale(1.05) rotate(1deg); }
            100% { transform: scale(1) rotate(0deg); }
        }
        .animate-bounceIn { animation: bounceIn 0.6s ease-out forwards; }
        @keyframes glow {
            from { box-shadow: 0 0 5px rgba(234, 179, 8, 0.5); }
            to { box-shadow: 0 0 15px rgba(234, 179, 8, 0.8); }
        }
        .btn-gradient:hover, .card:hover, .accordion-header:hover { animation: glow 1s ease-in-out infinite alternate; }
        .profile-img:hover { transform: scale(1.05); }
        /* Button gradient slide effect */
        .btn-gradient {
            position: relative;
            overflow: hidden;
        }
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }
        .btn-gradient:hover::before { left: 100%; }
        /* Pulse animation for buttons */
        .hover-loop { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        /* Notification animation */
        @keyframes slideDownFadeOut {
            0% { opacity: 0; transform: translateY(-20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        .notification { animation: slideDownFadeOut 2.5s ease-in-out forwards; }
        /* Gradient border for image */
        .profile-img-container {
            position: relative;
            display: inline-block;
        }
        .profile-img-container::before {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border-radius: 50%;
            background: linear-gradient(45deg, #7c2d12, #eab308);
            z-index: -1;
        }
        /* Custom checkbox */
        .custom-checkbox input[type="checkbox"] {
            appearance: none;
            width: 1.25rem;
            height: 1.25rem;
            border: 2px solid #d1d5db;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }
        .custom-checkbox input[type="checkbox"]:checked {
            background-color: #eab308;
            border-color: #7c2d12;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='white'%3E%3Cpath fill-rule='evenodd' d='M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293a1 1 0 00-1.414 1.414l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z' clip-rule='evenodd'/%3E%3C/svg%3E");
            background-size: 1rem;
        }
        /* Accordion animation */
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .accordion-content.open {
            max-height: 1000px; /* Adjust based on content */
        }
        /* Print styles */
        @media print {
            body { background-color: #fff; padding: 0; }
            .container { box-shadow: none; border: none; padding: 0; margin: 0; }
            .no-print, .action-bar, .accordion-header { display: none; }
            .accordion-content { max-height: none !important; display: block !important; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Sidebar -->
    <?php include('navbar.php'); ?>

    <!-- Main Content -->
    <main class="ml-[68px] p-6">
        <!-- Notifications -->
        <?php if (isset($message) && $message): ?>
            <div id="<?php echo $messageType === 'success' ? 'successNotification' : 'errorNotification'; ?>" 
                 class="notification fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-gradient-to-r 
                 <?php echo $messageType === 'success' ? 'from-green-500 to-green-600' : 'from-red-500 to-red-600'; ?> 
                 text-white px-6 py-3 rounded-md shadow-lg text-sm">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="container max-w-5xl mx-auto bg-white p-8 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 animate-bounceIn">
            <form method="POST" enctype="multipart/form-data" id="editProfileForm">
                <!-- Profile Header -->
                <div class="profile-header bg-gradient-to-r from-yellow-900 to-yellow-700 text-white p-6 rounded-t-lg flex flex-col sm:flex-row items-center gap-4">
                    <div class="profile-img-container relative">
                        <?php if (!empty($teacher_details['profile_image']) && file_exists($teacher_details['profile_image'])): ?>
                            <img id="profileImagePreview" src="<?php echo htmlspecialchars($teacher_details['profile_image']); ?>" alt="Profile Image of <?php echo htmlspecialchars($teacher_details['full_name']); ?>" class="profile-img w-32 h-32 object-cover rounded-full transition-transform duration-300">
                        <?php else: ?>
                            <div id="profileImagePreview" class="w-32 h-32 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-sm">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="text-center sm:text-left">
                        <h2 class="text-2xl font-bold">Edit <?php echo htmlspecialchars($teacher_details['full_name']); ?>'s Profile</h2>
                        <p class="text-sm">Update teacher details below</p>
                    </div>
                </div>

                <!-- Accordion Sections -->
                <div class="accordion mt-6">
                    <!-- General Information -->
                    <div class="accordion-item mb-4">
                        <button type="button" class="accordion-header w-full bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200 text-left text-lg font-semibold text-yellow-700 flex items-center" aria-expanded="true" aria-controls="general-content">
                            <i class='bx bx-user mr-2 text-yellow-600'></i> General Information
                            <i class='bx bx-chevron-down ml-auto text-yellow-600'></i>
                        </button>
                        <div id="general-content" class="accordion-content open grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-image mr-2 text-yellow-600'></i> Profile Image
                                </label>
                                <input type="file" name="profile_image" accept="image/*" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" onchange="previewImage(event)" aria-label="Profile Image">
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-user mr-2 text-yellow-600'></i> Full Name
                                </label>
                                <input type="text" name="full_name" value="<?php echo htmlspecialchars($teacher_details['full_name']); ?>" required 
                                       class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" aria-label="Full Name">
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-star mr-2 text-yellow-600'></i> Rank
                                </label>
                                <input type="text" name="rank" value="<?php echo htmlspecialchars($teacher_details['rank'] ?? ''); ?>" 
                                       class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" placeholder="Enter rank" aria-label="Rank">
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-male-female mr-2 text-yellow-600'></i> Gender
                                </label>
                                <select name="gender" required class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" aria-label="Gender">
                                    <option value="Male" <?php echo $teacher_details['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $teacher_details['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-calendar mr-2 text-yellow-600'></i> Date of Birth
                                </label>
                                <input type="date" name="date_of_birth" value="<?php echo htmlspecialchars($teacher_details['date_of_birth']); ?>" required 
                                       class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" aria-label="Date of Birth">
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-home mr-2 text-yellow-600'></i> Postal Address
                                </label>
                                <textarea name="postal_address" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" rows="3" aria-label="Postal Address"><?php echo htmlspecialchars($teacher_details['postal_address']); ?></textarea>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-id-card mr-2 text-yellow-600'></i> NIC Number
                                </label>
                                <input type="text" name="nic_number" value="<?php echo htmlspecialchars($teacher_details['nic_number']); ?>" required 
                                       class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" aria-label="NIC Number">
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-heart mr-2 text-yellow-600'></i> Marital Status
                                </label>
                                <select name="marital_status" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" aria-label="Marital Status">
                                    <option value="Single" <?php echo $teacher_details['marital_status'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married" <?php echo $teacher_details['marital_status'] === 'Married' ? 'selected' : ''; ?>>Married</option>
                                </select>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-phone mr-2 text-yellow-600'></i> WhatsApp Number
                                </label>
                                <input type="text" name="whatsapp_number" value="<?php echo htmlspecialchars($teacher_details['whatsapp_number']); ?>" 
                                       class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" aria-label="WhatsApp Number">
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-phone mr-2 text-yellow-600'></i> Residence Number
                                </label>
                                <input type="text" name="residence_number" value="<?php echo htmlspecialchars($teacher_details['residence_number']); ?>" 
                                       class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" aria-label="Residence Number">
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-comment mr-2 text-yellow-600'></i> First Language
                                </label>
                                <input type="text" name="first_language" value="<?php echo htmlspecialchars($teacher_details['first_language']); ?>" 
                                       class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" aria-label="First Language">
                            </div>
                        </div>
                    </div>

                    <!-- Educational Details -->
                    <div class="accordion-item mb-4">
                        <button type="button" class="accordion-header w-full bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200 text-left text-lg font-semibold text-yellow-700 flex items-center" aria-expanded="false" aria-controls="education-content">
                            <i class='bx bx-book-alt mr-2 text-yellow-600'></i> Educational Details
                            <i class='bx bx-chevron-down ml-auto text-yellow-600'></i>
                        </button>
                        <div id="education-content" class="accordion-content grid grid-cols-1 gap-4 p-4">
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-book-alt mr-2 text-yellow-600'></i> Other Educational Qualifications
                                </label>
                                <textarea name="other_qualifications" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" rows="4" aria-label="Other Educational Qualifications"><?php echo htmlspecialchars($teacher_details['other_educational_qualification']); ?></textarea>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-briefcase mr-2 text-yellow-600'></i> Professional Qualifications
                                </label>
                                <textarea name="professional_qualifications" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" rows="4" aria-label="Professional Qualifications"><?php echo htmlspecialchars($teacher_details['professional_qualification']); ?></textarea>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-trophy mr-2 text-yellow-600'></i> Extra Curricular Activities
                                </label>
                                <textarea name="activities" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" rows="4" aria-label="Extra Curricular Activities"><?php echo htmlspecialchars($teacher_details['extra_curricular_activities']); ?></textarea>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-workflow mr-2 text-yellow-600'></i> Work Experience
                                </label>
                                <textarea name="experience" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" rows="4" aria-label="Work Experience"><?php echo htmlspecialchars($teacher_details['work_experience']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Teaching Assignment -->
                    <div class="accordion-item mb-4">
                        <button type="button" class="accordion-header w-full bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200 text-left text-lg font-semibold text-yellow-700 flex items-center" aria-expanded="false" aria-controls="teaching-content">
                            <i class='bx bx-chalkboard mr-2 text-yellow-600'></i> Teaching Assignment
                            <i class='bx bx-chevron-down ml-auto text-yellow-600'></i>
                        </button>
                        <div id="teaching-content" class="accordion-content grid grid-cols-1 gap-4 p-4">
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-chalkboard mr-2 text-yellow-600'></i> Subject
                                </label>
                                <select id="subject" name="subject" required class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" aria-label="Subject">
                                    <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['id']; ?>" <?php echo $teacher_details['subject_id'] == $subject['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-book mr-2 text-yellow-600'></i> Class Teacher Grade
                                </label>
                                <select name="class_teacher_grade" class="p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 w-full" aria-label="Class Teacher Grade">
                                    <?php foreach ($grades as $grade): ?>
                                        <option value="<?php echo $grade['id']; ?>" <?php echo $teacher_details['grade_id'] == $grade['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($grade['grade_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <label class="flex items-center text-sm font-medium text-gray-700 mb-2">
                                    <i class='bx bx-book-open mr-2 text-yellow-600'></i> Teaching Grades
                                </label>
                                <div id="grades" class="space-y-2">
                                    <?php foreach ($grades as $grade): ?>
                                        <label class="flex items-center text-sm text-gray-600 custom-checkbox">
                                            <input type="checkbox" name="grades[]" value="<?php echo $grade['id']; ?>" <?php echo in_array($grade['id'], $current_classes) ? 'checked' : ''; ?> 
                                                   class="mr-2">
                                            <?php echo htmlspecialchars($grade['grade_name']); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Floating Action Bar -->
                <div class="action-bar no-print fixed bottom-4 right-4 z-40 flex flex-col sm:flex-row gap-2">
                    <button type="submit" class="p-2 bg-gradient-to-r from-yellow-900 to-yellow-700 text-white rounded-md btn-gradient hover:bg-yellow-800 transition duration-200 hover-loop" aria-label="Save profile changes">
                        <i class='bx bx-save mr-1'></i> Save Changes
                    </button>
                    <a href="./teacher_profile.php?username=<?php echo urlencode($teacher_details['username']); ?>" 
                       class="p-2 bg-gradient-to-r from-gray-500 to-gray-700 text-white rounded-md btn-gradient hover:bg-gray-800 transition duration-200 hover-loop" 
                       aria-label="Cancel and return to profile">
                        <i class='bx bx-arrow-back mr-1'></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Auto-hide notifications
        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 2500);
            });

            // Accordion toggle
            const accordionHeaders = document.querySelectorAll('.accordion-header');
            accordionHeaders.forEach(header => {
                header.addEventListener('click', (event) => {
                    event.preventDefault(); // Prevent form submission
                    const content = header.nextElementSibling;
                    const isOpen = content.classList.contains('open');
                    // Close all other sections
                    document.querySelectorAll('.accordion-content').forEach(c => {
                        c.classList.remove('open');
                        c.previousElementSibling.querySelector('.bx-chevron-down').classList.remove('rotate-180');
                        c.previousElementSibling.setAttribute('aria-expanded', 'false');
                    });
                    // Toggle current section
                    if (!isOpen) {
                        content.classList.add('open');
                        header.querySelector('.bx-chevron-down').classList.add('rotate-180');
                        header.setAttribute('aria-expanded', 'true');
                    }
                });
            });

            // Fetch grades on subject change
            document.getElementById('subject').addEventListener('change', function(event) {
                event.preventDefault(); // Prevent any form submission
                const subjectId = this.value;
                const gradesContainer = document.getElementById('grades');
                gradesContainer.innerHTML = '<p class="text-gray-500 italic">Loading grades...</p>';

                fetch(`fetch_grades.php?subject_id=${subjectId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to fetch grades');
                        }
                        return response.json();
                    })
                    .then(data => {
                        gradesContainer.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(grade => {
                                gradesContainer.innerHTML += `
                                    <label class="flex items-center text-sm text-gray-600 custom-checkbox">
                                        <input type="checkbox" name="grades[]" value="${grade.grade_id}" ${grade.is_assigned ? 'checked' : ''} class="mr-2">
                                        ${grade.grade_name}
                                    </label>
                                `;
                            });
                        } else {
                            gradesContainer.innerHTML = '<p class="text-gray-500 italic">No grades available for this subject.</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching grades:', error);
                        gradesContainer.innerHTML = '<p class="text-red-500 italic">Error loading grades.</p>';
                    });
            });

            // Image preview
            function previewImage(event) {
                event.preventDefault(); // Prevent form submission
                const preview = document.getElementById('profileImagePreview');
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.classList.add('w-32', 'h-32', 'object-cover', 'rounded-full', 'transition-transform', 'duration-300', 'profile-img');
                        preview.classList.remove('bg-gray-200', 'flex', 'items-center', 'justify-center', 'text-gray-500', 'text-sm');
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    </script>
</body>
</html>
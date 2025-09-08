<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'database_connection.php';

// Get username filter from query parameter
$username_filter = isset($_GET['username']) ? $_GET['username'] : '';

// Fetch user details, including teaching_classes as grade IDs
$sql_user = "
    SELECT 
        t.*, 
        u.active, 
        s.subject_name, 
        ed.other_educational_qualification, 
        ed.professional_qualification, 
        ed.extra_curricular_activities, 
        ed.work_experience 
    FROM noclass_teacher t
    LEFT JOIN user u ON t.username = u.username
    LEFT JOIN subjects s ON t.subject_id = s.id
    LEFT JOIN educational_details ed ON t.username = ed.username
    WHERE t.username = ?";

$stmt = $conn->prepare($sql_user);
$stmt->bind_param("s", $username_filter);
$stmt->execute();
$user_details = $stmt->get_result()->fetch_assoc();

// Decode teaching_classes JSON to get grade IDs
$teaching_classes_ids = [];
if (!empty($user_details['teaching_classes'])) {
    $teaching_classes_ids = json_decode($user_details['teaching_classes'], true); // Convert JSON to array
}

// Fetch grade names for teaching_classes
$teaching_classes = [];
if (!empty($teaching_classes_ids)) {
    $placeholders = implode(',', array_fill(0, count($teaching_classes_ids), '?')); // Create placeholders for IN clause
    $sql_grades = "SELECT grade_name FROM grades WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql_grades);

    // Bind the grade IDs dynamically
    $types = str_repeat('i', count($teaching_classes_ids)); // Generate types string
    $stmt->bind_param($types, ...$teaching_classes_ids); // Bind grade IDs

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $teaching_classes[] = $row['grade_name']; // Collect grade names
    }
}

// Fetch GCE O/L Results
$ol_query = "SELECT * FROM ol_result_teacher WHERE username = ?";
$ol_stmt = $conn->prepare($ol_query);
$ol_stmt->bind_param("s", $username_filter);
$ol_stmt->execute();
$ol_results = $ol_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch GCE A/L Results
$al_query = "SELECT * FROM al_result_teacher WHERE username = ?";
$al_stmt = $conn->prepare($al_query);
$al_stmt->bind_param("s", $username_filter);
$al_stmt->execute();
$al_results = $al_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch previous experience
$experience_query = "SELECT * FROM previous_info WHERE username = ?";
$exp_stmt = $conn->prepare($experience_query);
$exp_stmt->bind_param("s", $username_filter);
$exp_stmt->execute();
$previous_info = $exp_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch classes the teacher is teaching from 'teaching_classes' column
$classes_query = "SELECT teaching_classes FROM noclass_teacher WHERE username = ?";
$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->bind_param("s", $username_filter);
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result()->fetch_assoc();
$classes_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['block']) && !empty($_POST['username']) && !empty($_POST['block_reason'])) {
        $username = $_POST['username'];
        $block_reason = $_POST['block_reason'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update user table to set active = 0
            $sql_block = "UPDATE user SET active = 0 WHERE username = ?";
            $stmt = $conn->prepare($sql_block);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();

            // Insert the block reason into block_reasons table
            $sql_reason = "INSERT INTO block_reasons (username, block_reason) VALUES (?, ?)";
            $stmt = $conn->prepare($sql_reason);
            $stmt->bind_param("ss", $username, $block_reason);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            echo "<script>
                    alert('User has been blocked successfully.');
                    window.location.href = 'blocks.php'; // Redirect to a page, e.g., user list
                  </script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p>Error blocking user: " . $e->getMessage() . "</p>";
        }
    }

    if (isset($_POST['activate']) && !empty($_POST['username'])) {
        $username = $_POST['username'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update user table to set active = 1
            $sql_activate = "UPDATE user SET active = 1 WHERE username = ?";
            $stmt = $conn->prepare($sql_activate);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();

            // Delete the block reason from block_reasons table
            $sql_delete_reason = "DELETE FROM block_reasons WHERE username = ?";
            $stmt = $conn->prepare($sql_delete_reason);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            echo "<script>
                    alert('User has been Unblocked successfully.');
                    window.location.href = 'view_teachers_staff.php'; // Redirect to a page, e.g., user list
                  </script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p>Error activating user: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Substitute Teacher Profile</title>
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
        .btn-gradient:hover, .card:hover { animation: glow 1s ease-in-out infinite alternate; }
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
        /* Tab styling */
        .tab-button.active {
            background: linear-gradient(to right, #7c2d12, #eab308);
            color: white;
        }
        /* Print styles */
        @media print {
            body { background-color: #fff; padding: 0; }
            .container { box-shadow: none; border: none; padding: 0; margin: 0; }
            .no-print, #blockModalOverlay, #blockModal, .tabs, .action-bar { display: none; }
            .tab-content { display: block !important; }
            .tab-content > div { margin-bottom: 1rem; }
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
            <?php if ($user_details): ?>
                <!-- Profile Header -->
                <div class="profile-header bg-gradient-to-r from-yellow-900 to-yellow-700 text-white p-6 rounded-t-lg flex flex-col sm:flex-row items-center gap-4">
                    <div class="profile-img-container relative">
                        <?php if (!empty($user_details['profile_image']) && file_exists($user_details['profile_image'])): ?>
                            <img src="<?php echo htmlspecialchars($user_details['profile_image']); ?>" alt="Profile Image of <?php echo htmlspecialchars($user_details['full_name']); ?>" class="profile-img w-40 h-40 object-cover rounded-full transition-transform duration-300">
                        <?php else: ?>
                            <img src="./Resources/default_profile.png" alt="Default Profile Image" class="profile-img w-40 h-40 object-cover rounded-full transition-transform duration-300">
                        <?php endif; ?>
                        <span class="absolute top-0 right-0 bg-<?php echo $user_details['active'] == 1 ? 'green-600' : 'red-600'; ?> text-white text-xs font-semibold px-2 py-1 rounded-full">
                            <?php echo $user_details['active'] == 1 ? 'Active' : 'Blocked'; ?>
                        </span>
                    </div>
                    <div class="text-center sm:text-left">
                        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($user_details['full_name']); ?></h2>
                        <p class="text-sm"><?php echo htmlspecialchars($user_details['subject_name'] ?? 'N/A'); ?> | Substitute Teacher</p>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="action-bar no-print sticky top-0 z-40 bg-gray-50 p-4 rounded-b-lg shadow-md flex flex-wrap justify-center gap-2 mb-6">
                    <button onclick="window.print()" class="p-2 bg-gradient-to-r from-yellow-900 to-yellow-700 text-white rounded-md btn-gradient hover:bg-yellow-800 transition duration-200 hover-loop" aria-label="Print profile">
                        <i class='bx bx-printer mr-1'></i> Print
                    </button>
                    <a href="./edit_sub_teacher_profile.php?username=<?php echo urlencode($username_filter); ?>" 
                       class="p-2 bg-gradient-to-r from-yellow-900 to-yellow-700 text-white rounded-md btn-gradient hover:bg-yellow-800 transition duration-200 hover-loop" 
                       aria-label="Edit profile">
                        <i class='bx bx-edit mr-1'></i> Edit
                    </a>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this profile?');" class="inline">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($username_filter); ?>">
                        <button type="submit" name="delete" class="p-2 bg-gradient-to-r from-red-600 to-red-800 text-white rounded-md btn-gradient hover:bg-red-900 transition duration-200 hover-loop" aria-label="Delete profile">
                            <i class='bx bx-trash mr-1'></i> Delete
                        </button>
                    </form>
                    <?php if ($user_details['active'] == 1): ?>
                        <button onclick="openBlockModal('<?php echo htmlspecialchars($username_filter); ?>')" 
                                class="p-2 bg-gradient-to-r from-orange-500 to-orange-700 text-white rounded-md btn-gradient hover:bg-orange-800 transition duration-200 hover-loop" 
                                aria-label="Block user">
                            <i class='bx bx-block mr-1'></i> Block
                        </button>
                    <?php else: ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to activate this user?');" class="inline">
                            <input type="hidden" name="username" value="<?php echo htmlspecialchars($username_filter); ?>">
                            <button type="submit" name="activate" class="p-2 bg-gradient-to-r from-green-500 to-green-700 text-white rounded-md btn-gradient hover:bg-green-800 transition duration-200 hover-loop" aria-label="Activate user">
                                <i class='bx bx-check-circle mr-1'></i> Activate
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Tabs -->
                <div class="tabs no-print flex flex-wrap border-b border-gray-200 mb-6">
                    <button class="tab-button px-4 py-2 text-sm font-medium text-gray-700 hover:bg-yellow-100 transition duration-200 active" data-tab="personal" aria-label="Personal Information">Personal Info</button>
                    <button class="tab-button px-4 py-2 text-sm font-medium text-gray-700 hover:bg-yellow-100 transition duration-200" data-tab="teaching" aria-label="Teaching Details">Teaching Details</button>
                    <button class="tab-button px-4 py-2 text-sm font-medium text-gray-700 hover:bg-yellow-100 transition duration-200" data-tab="qualifications" aria-label="Qualifications">Qualifications</button>
                    <button class="tab-button px-4 py-2 text-sm font-medium text-gray-700 hover:bg-yellow-100 transition duration-200" data-tab="results" aria-label="Exam Results">Exam Results</button>
                </div>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Personal Info -->
                    <div id="personal" class="tab-pane grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 animate-bounceIn">
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <div class="flex items-start">
                                <i class='bx bx-user mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Full Name:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['full_name']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <div class="flex items-start">
                                <i class='bx bx-id-card mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Username:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['username']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <div class="flex items-start">
                                <i class='bx bx-male-female mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Gender:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['gender']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <div class="flex items-start">
                                <i class='bx bx-calendar mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Date of Birth:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['date_of_birth']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <div class="flex items-start">
                                <i class='bx bx-home mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Address:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['postal_address']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <div class="flex items-start">
                                <i class='bx bx-globe mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Ethnicity:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['ethnicity']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <div class="flex items-start">
                                <i class='bx bx-id-card mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">NIC Number:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['nic_number']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <div class="flex items-start">
                                <i class='bx bx-heart mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Marital Status:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['marital_status']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <div class="flex items-start">
                                <i class='bx bx-phone mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">WhatsApp Number:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['whatsapp_number']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <div class="flex items-start">
                                <i class='bx bx-phone mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Residence Number:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['residence_number']); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <div class="flex items-start">
                                <i class='bx bx-comment mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">First Language:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['first_language']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Teaching Details -->
                    <div id="teaching" class="tab-pane hidden mb-6 animate-bounceIn">
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200 mb-4">
                            <div class="flex items-start">
                                <i class='bx bx-chalkboard mr-2 text-yellow-600 text-lg'></i>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Assigned Subject:</label>
                                    <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['subject_name'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <h3 class="text-lg font-semibold text-yellow-700 mb-2">Teaching Classes</h3>
                            <?php if (!empty($teaching_classes)): ?>
                                <?php foreach ($teaching_classes as $index => $class): ?>
                                    <div class="flex items-center text-sm text-gray-600 mb-2">
                                        <i class='bx bx-book-open mr-2 text-yellow-600'></i>
                                        <span><?php echo htmlspecialchars(trim($class)); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-500 italic">No classes assigned.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Qualifications -->
                    <div id="qualifications" class="tab-pane hidden mb-6 animate-bounceIn">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <div class="flex items-start">
                                    <i class='bx bx-book-alt mr-2 text-yellow-600 text-lg'></i>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Other Qualifications:</label>
                                        <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['other_educational_qualification'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <div class="flex items-start">
                                    <i class='bx bx-briefcase mr-2 text-yellow-600 text-lg'></i>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Professional Qualifications:</label>
                                        <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['professional_qualification'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <div class="flex items-start">
                                    <i class='bx bx-trophy mr-2 text-yellow-600 text-lg'></i>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Extra Curricular Activities:</label>
                                        <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['extra_curricular_activities'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                                <div class="flex items-start">
                                    <i class='bx bx-workflow mr-2 text-yellow-600 text-lg'></i>
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">Work Experience:</label>
                                        <span class="block text-sm text-gray-600"><?php echo htmlspecialchars($user_details['work_experience'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200 md:col-span-2">
                                <h3 class="text-lg font-semibold text-yellow-700 mb-2">Previous Work Experience</h3>
                                <?php if ($previous_info): ?>
                                    <?php foreach ($previous_info as $index => $info): ?>
                                        <div class="flex items-center text-sm text-gray-600 mb-2">
                                            <i class='bx bx-briefcase mr-2 text-yellow-600'></i>
                                            <span><?php echo htmlspecialchars($info['previous_role']); ?> at <?php echo htmlspecialchars($info['previous_company']); ?> (<?php echo htmlspecialchars($info['years_experience']); ?> years)</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-gray-500 italic">No previous experience found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Exam Results -->
                    <div id="results" class="tab-pane hidden mb-6 animate-bounceIn">
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200 mb-4">
                            <h3 class="text-lg font-semibold text-yellow-700 mb-2">GCE O/L Results</h3>
                            <?php if ($ol_results): ?>
                                <table class="w-full border-collapse border border-gray-200">
                                    <thead>
                                        <tr class="bg-gradient-to-r from-yellow-900 to-yellow-700 text-white">
                                            <th class="p-3 text-left">Subject</th>
                                            <th class="p-3 text-left">Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ol_results as $index => $ol): ?>
                                            <tr class="hover:bg-yellow-50 transition duration-200">
                                                <td class="p-3 border border-gray-200"><?php echo htmlspecialchars($ol['subject_name']); ?></td>
                                                <td class="p-3 border border-gray-200"><?php echo htmlspecialchars($ol['result']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-gray-500 italic">No O/L results found.</p>
                            <?php endif; ?>
                        </div>
                        <div class="card bg-gray-50 p-4 rounded-md shadow hover:shadow-lg transition duration-200">
                            <h3 class="text-lg font-semibold text-yellow-700 mb-2">GCE A/L Results</h3>
                            <?php if ($al_results): ?>
                                <table class="w-full border-collapse border border-gray-200">
                                    <thead>
                                        <tr class="bg-gradient-to-r from-yellow-900 to-yellow-700 text-white">
                                            <th class="p-3 text-left">Subject</th>
                                            <th class="p-3 text-left">Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($al_results as $index => $al): ?>
                                            <tr class="hover:bg-yellow-50 transition duration-200">
                                                <td class="p-3 border border-gray-200"><?php echo htmlspecialchars($al['subject_name']); ?></td>
                                                <td class="p-3 border border-gray-200"><?php echo htmlspecialchars($al['result']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p class="text-gray-500 italic">No A/L results found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="no-print text-center mt-6">
                    <a href="./view_teachers_staff.php" class="text-sm text-yellow-700 hover:underline" aria-label="Back to teachers and staff list">Back to Teachers & Staff</a>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500 italic animate-bounceIn">Substitute teacher profile not found.</p>
            <?php endif; ?>
        </div>

        <!-- Block Modal -->
        <div id="blockModalOverlay" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50" onclick="closeBlockModal()"></div>
        <div id="blockModal" class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white p-6 rounded-lg shadow-lg hidden z-50 animate-bounceIn w-full max-w-md">
            <form method="POST" id="blockForm" class="flex flex-col">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Block User</h3>
                <input type="hidden" name="username" id="blockUsername">
                <label for="blockReason" class="text-sm font-medium text-gray-700 mb-2">Reason for Blocking:</label>
                <textarea name="block_reason" id="blockReason" rows="4" class="p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200" required></textarea>
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="submit" name="block" class="p-2 bg-gradient-to-r from-red-600 to-red-800 text-white rounded-md btn-gradient hover:bg-red-900 transition duration-200 hover-loop">Submit</button>
                    <button type="button" onclick="closeBlockModal()" class="p-2 bg-gradient-to-r from-yellow-900 to-yellow-700 text-white rounded-md btn-gradient hover:bg-yellow-800 transition duration-200 hover-loop">Cancel</button>
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

            // Tab navigation
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabPanes = document.querySelectorAll('.tab-pane');
            tabButtons.forEach(button => {
                button.addEventListener('click', (event) => {
                    event.preventDefault(); // Prevent any form submission
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    tabPanes.forEach(pane => pane.classList.add('hidden'));
                    document.getElementById(button.dataset.tab).classList.remove('hidden');
                });
            });
        });

        // Block modal functions
        function openBlockModal(username) {
            document.getElementById('blockUsername').value = username;
            document.getElementById('blockModalOverlay').style.display = 'block';
            document.getElementById('blockModal').style.display = 'block';
        }

        function closeBlockModal() {
            document.getElementById('blockModalOverlay').style.display = 'none';
            document.getElementById('blockModal').style.display = 'none';
        }
    </script>
</body>
</html>
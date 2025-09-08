<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include("database_connection.php");

// Initialize filters
$user_id_filter = isset($_GET['username']) ? $_GET['username'] : null;

// Fetch Teachers Data (Assigned Class)
$teachers = [];
$teachers_sql = "
    SELECT 
        teacher.id,
        teacher.username,
        teacher.full_name,
        teacher.gender,
        teacher.date_of_birth,
        teacher.postal_address,
        teacher.ethnicity,
        teacher.nic_number,
        teacher.marital_status,
        teacher.whatsapp_number,
        teacher.residence_number,
        teacher.first_language,
        teacher.profile_image,
        user.active,
        grades.grade_name,
        subjects.subject_name
    FROM 
        teacher
    LEFT JOIN 
        grades ON teacher.grade_id = grades.id
    LEFT JOIN 
        subjects ON teacher.subject_id = subjects.id
    LEFT JOIN 
        user ON teacher.username = user.username
    WHERE 
        teacher.grade_id IS NOT NULL
";

if ($user_id_filter) {
    $teachers_sql .= " AND teacher.id = ?";
}

$teachers_sql .= " ORDER BY teacher.full_name ASC";

if ($stmt = $conn->prepare($teachers_sql)) {
    if ($user_id_filter) {
        $stmt->bind_param("i", $user_id_filter);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
    $stmt->close();
}

// Fetch Sub Teachers (NoClass_Teacher)
$sub_teachers = [];
$sub_teachers_sql = "
    SELECT 
        noclass_teacher.id,
        noclass_teacher.username,
        noclass_teacher.full_name,
        noclass_teacher.gender,
        noclass_teacher.date_of_birth,
        noclass_teacher.postal_address,
        noclass_teacher.ethnicity,
        noclass_teacher.nic_number,
        noclass_teacher.marital_status,
        noclass_teacher.whatsapp_number,
        noclass_teacher.residence_number,
        noclass_teacher.first_language,
        noclass_teacher.profile_image,
        user.active,
        subjects.subject_name
    FROM 
        noclass_teacher
    LEFT JOIN 
        subjects ON noclass_teacher.subject_id = subjects.id
    LEFT JOIN 
        user ON noclass_teacher.username = user.username
";

if ($stmt = $conn->prepare($sub_teachers_sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $sub_teachers[] = $row;
    }
    $stmt->close();
}

// Fetch Staff Data (Including Accountants)
$staff = [];
$staff_sql = "
    SELECT 
        staff.id,
        staff.username,
        staff.full_name,
        staff.gender,
        staff.date_of_birth,
        staff.postal_address,
        staff.ethnicity,
        staff.nic_number,
        staff.marital_status,
        staff.whatsapp_number,
        staff.residence_number,
        staff.first_language,
        staff.profile_image,
        staff.position,
        user.active
    FROM 
        staff
    LEFT JOIN 
        user ON staff.username = user.username
";

$accountants_sql = "
    SELECT 
        accountant.id,
        accountant.username,
        accountant.full_name,
        accountant.gender,
        accountant.date_of_birth,
        accountant.postal_address,
        accountant.ethnicity,
        accountant.nic_number,
        accountant.marital_status,
        accountant.whatsapp_number,
        accountant.residence_number,
        accountant.first_language,
        accountant.profile_image,
        'Accountant' AS position,
        accountant.active
    FROM 
        accountant
    LEFT JOIN 
        user ON accountant.username = user.username
";

if ($user_id_filter) {
    $staff_sql .= " WHERE staff.id = ?";
    $accountants_sql .= " WHERE accountant.id = ?";
}

// Fetch and merge staff and accountants
$staff_sql .= " ORDER BY staff.full_name ASC";
$accountants_sql .= " ORDER BY accountant.full_name ASC";

// Execute staff query
if ($stmt = $conn->prepare($staff_sql)) {
    if ($user_id_filter) {
        $stmt->bind_param("i", $user_id_filter);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $staff[] = $row;
    }
    $stmt->close();
}

// Execute accountants query
$accountants = [];  // Separate array for accountants
if ($stmt = $conn->prepare($accountants_sql)) {
    if ($user_id_filter) {
        $stmt->bind_param("i", $user_id_filter);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $accountants[] = $row;  // Store in accountants array
    }
    $stmt->close();
}

// Close the connection
$conn->close();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Teachers, Sub Teachers, Staff, and Accountants</title>
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
        .card:hover { 
            animation: glow 1s ease-in-out infinite alternate;
            background: linear-gradient(to bottom, #ffffff, #fef9e7);
        }
        .card img:hover {
            transform: scale(1.05);
        }
        /* Notification animation (for future use) */
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Sidebar -->
    <?php include('navbar.php'); ?>

    <!-- Main Content -->
    <main class="ml-[68px] p-6">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-10">View Teachers, Sub Teachers, Staff, and Accountants</h2>

            <!-- Teachers Section -->
            <div class="section mb-12">
                <h3 class="text-xl font-semibold text-yellow-700 mb-6">Teachers</h3>
                <?php if (!empty($teachers)): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php foreach ($teachers as $index => $teacher): ?>
                            <a class="card bg-white rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 animate-bounceIn <?php echo $teacher['active'] == 0 ? 'opacity-50' : ''; ?>" 
                               href="./teacher_profile.php?username=<?php echo urlencode($teacher['username']); ?>" 
                               aria-label="View profile of <?php echo htmlspecialchars($teacher['full_name']); ?>"
                               style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                <div class="relative p-4">
                                    <div class="profile-img-container mx-auto mb-4">
                                        <?php if (!empty($teacher['profile_image']) && file_exists($teacher['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($teacher['profile_image']); ?>" alt="Profile Image of <?php echo htmlspecialchars($teacher['full_name']); ?>" class="w-32 h-32 object-cover rounded-full transition-transform duration-300">
                                        <?php else: ?>
                                            <img src="./Resources/default_profile.png" alt="Default Profile Image" class="w-32 h-32 object-cover rounded-full transition-transform duration-300">
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($teacher['active'] == 0): ?>
                                        <span class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 rotate-12 bg-gradient-to-r from-red-600 to-red-800 text-white text-xs font-semibold px-3 py-1 rounded shadow">Blocked</span>
                                    <?php endif; ?>
                                </div>
                                <div class="border-t border-gray-200 pt-4 pb-4 px-4">
                                    <h3 class="text-lg font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($teacher['full_name']); ?></h3>
                                    <div class="flex items-center text-sm text-gray-600 mb-1">
                                        <i class='bx bx-book mr-2 text-yellow-600'></i>
                                        <span class="font-medium">Grade:</span>&nbsp;<?php echo htmlspecialchars($teacher['grade_name'] ?? 'N/A'); ?>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600 mb-1">
                                        <i class='bx bx-chalkboard mr-2 text-yellow-600'></i>
                                        <span class="font-medium">Subject:</span>&nbsp;<?php echo htmlspecialchars($teacher['subject_name'] ?? 'N/A'); ?>
                                    </div>
                                    <?php if (!empty($teacher['whatsapp_number'])): ?>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class='bx bx-phone mr-2 text-yellow-600'></i>
                                            <span class="font-medium">WhatsApp:</span>&nbsp;<?php echo htmlspecialchars($teacher['whatsapp_number'] ?? 'N/A'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data text-center text-gray-500 italic text-lg">No teachers found.</p>
                <?php endif; ?>
            </div>

            <!-- Sub Teachers Section -->
            <div class="section mb-12">
                <h3 class="text-xl font-semibold text-yellow-700 mb-6">Sub Teachers</h3>
                <?php if (!empty($sub_teachers)): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php foreach ($sub_teachers as $index => $sub_teacher): ?>
                            <a class="card bg-white rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 animate-bounceIn <?php echo $sub_teacher['active'] == 0 ? 'opacity-50' : ''; ?>" 
                               href="./sub_teacher_profile.php?username=<?php echo urlencode($sub_teacher['username']); ?>" 
                               aria-label="View profile of <?php echo htmlspecialchars($sub_teacher['full_name']); ?>"
                               style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                <div class="relative p-4">
                                    <div class="profile-img-container mx-auto mb-4">
                                        <?php if (!empty($sub_teacher['profile_image']) && file_exists($sub_teacher['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($sub_teacher['profile_image']); ?>" alt="Profile Image of <?php echo htmlspecialchars($sub_teacher['full_name']); ?>" class="w-32 h-32 object-cover rounded-full transition-transform duration-300">
                                        <?php else: ?>
                                            <img src="./Resources/default_profile.png" alt="Default Profile Image" class="w-32 h-32 object-cover rounded-full transition-transform duration-300">
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($sub_teacher['active'] == 0): ?>
                                        <span class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 rotate-12 bg-gradient-to-r from-red-600 to-red-800 text-white text-xs font-semibold px-3 py-1 rounded shadow">Blocked</span>
                                    <?php endif; ?>
                                </div>
                                <div class="border-t border-gray-200 pt-4 pb-4 px-4">
                                    <h3 class="text-lg font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($sub_teacher['full_name']); ?></h3>
                                    <div class="flex items-center text-sm text-gray-600 mb-1">
                                        <i class='bx bx-chalkboard mr-2 text-yellow-600'></i>
                                        <span class="font-medium">Subject:</span>&nbsp;<?php echo htmlspecialchars($sub_teacher['subject_name'] ?? 'N/A'); ?>
                                    </div>
                                    <?php if (!empty($sub_teacher['whatsapp_number'])): ?>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class='bx bx-phone mr-2 text-yellow-600'></i>
                                            <span class="font-medium">WhatsApp:</span>&nbsp;<?php echo htmlspecialchars($sub_teacher['whatsapp_number'] ?? 'N/A'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data text-center text-gray-500 italic text-lg">No sub teachers found.</p>
                <?php endif; ?>
            </div>

            <!-- Staff Section -->
            <div class="section mb-12">
                <h3 class="text-xl font-semibold text-yellow-700 mb-6">Staff</h3>
                <?php if (!empty($staff)): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php foreach ($staff as $index => $member): ?>
                            <a class="card bg-white rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 animate-bounceIn <?php echo $member['active'] == 0 ? 'opacity-50' : ''; ?>" 
                               href="./staff_profile.php?username=<?php echo urlencode($member['username']); ?>" 
                               aria-label="View profile of <?php echo htmlspecialchars($member['full_name']); ?>"
                               style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                <div class="relative p-4">
                                    <div class="profile-img-container mx-auto mb-4">
                                        <?php if (!empty($member['profile_image']) && file_exists($member['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($member['profile_image']); ?>" alt="Profile Image of <?php echo htmlspecialchars($member['full_name']); ?>" class="w-32 h-32 object-cover rounded-full transition-transform duration-300">
                                        <?php else: ?>
                                            <img src="./Resources/default_profile.png" alt="Default Profile Image" class="w-32 h-32 object-cover rounded-full transition-transform duration-300">
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($member['active'] == 0): ?>
                                        <span class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 rotate-12 bg-gradient-to-r from-red-600 to-red-800 text-white text-xs font-semibold px-3 py-1 rounded shadow">Blocked</span>
                                    <?php endif; ?>
                                </div>
                                <div class="border-t border-gray-200 pt-4 pb-4 px-4">
                                    <h3 class="text-lg font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($member['full_name']); ?></h3>
                                    <div class="flex items-center text-sm text-gray-600 mb-1">
                                        <i class='bx bx-briefcase mr-2 text-yellow-600'></i>
                                        <span class="font-medium">Position:</span>&nbsp;<?php echo htmlspecialchars($member['position'] ?? 'Staff Member'); ?>
                                    </div>
                                    <?php if (!empty($member['whatsapp_number'])): ?>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class='bx bx-phone mr-2 text-yellow-600'></i>
                                            <span class="font-medium">WhatsApp:</span>&nbsp;<?php echo htmlspecialchars($member['whatsapp_number'] ?? 'N/A'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data text-center text-gray-500 italic text-lg">No staff members found.</p>
                <?php endif; ?>
            </div>

            <!-- Accountants Section -->
            <div class="section mb-12">
                <h3 class="text-xl font-semibold text-yellow-700 mb-6">Accountants</h3>
                <?php if (!empty($accountants)): ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        <?php foreach ($accountants as $index => $accountant): ?>
                            <a class="card bg-white rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 animate-bounceIn <?php echo $accountant['active'] == 0 ? 'opacity-50' : ''; ?>" 
                               href="./accountant_profile.php?username=<?php echo urlencode($accountant['username']); ?>" 
                               aria-label="View profile of <?php echo htmlspecialchars($accountant['full_name']); ?>"
                               style="animation-delay: <?php echo $index * 0.1; ?>s;">
                                <div class="relative p-4">
                                    <div class="profile-img-container mx-auto mb-4">
                                        <?php if (!empty($accountant['profile_image']) && file_exists($accountant['profile_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($accountant['profile_image']); ?>" alt="Profile Image of <?php echo htmlspecialchars($accountant['full_name']); ?>" class="w-32 h-32 object-cover rounded-full transition-transform duration-300">
                                        <?php else: ?>
                                            <img src="./Resources/default_profile.png" alt="Default Profile Image" class="w-32 h-32 object-cover rounded-full transition-transform duration-300">
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($accountant['active'] == 0): ?>
                                        <span class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 rotate-12 bg-gradient-to-r from-red-600 to-red-800 text-white text-xs font-semibold px-3 py-1 rounded shadow">Blocked</span>
                                    <?php endif; ?>
                                </div>
                                <div class="border-t border-gray-200 pt-4 pb-4 px-4">
                                    <h3 class="text-lg font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($accountant['full_name']); ?></h3>
                                    <div class="flex items-center text-sm text-gray-600 mb-1">
                                        <i class='bx bx-calculator mr-2 text-yellow-600'></i>
                                        <span class="font-medium">Position:</span>&nbsp;<?php echo htmlspecialchars($accountant['position'] ?? 'Accountant'); ?>
                                    </div>
                                    <?php if (!empty($accountant['whatsapp_number'])): ?>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class='bx bx-phone mr-2 text-yellow-600'></i>
                                            <span class="font-medium">WhatsApp:</span>&nbsp;<?php echo htmlspecialchars($accountant['whatsapp_number'] ?? 'N/A'); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-data text-center text-gray-500 italic text-lg">No accountants found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
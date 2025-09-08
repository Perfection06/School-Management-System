<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include("database_connection.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $full_name = $_POST['full_name'];
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $postal_address = $_POST['postal_address'];
    $ethnicity = $_POST['ethnicity'];
    $nic_number = $_POST['nic_number'];
    $marital_status = $_POST['marital_status'];
    $whatsapp_number = $_POST['whatsapp_number'];
    $residence_number = $_POST['residence_number'];
    $first_language = $_POST['first_language'];
    $other_educational_qualification = $_POST['other_educational_qualification'];
    $professional_qualification = $_POST['professional_qualification'];
    $extra_curricular_activities = $_POST['extra_curricular_activities'];
    $work_experience = $_POST['work_experience'];

    // For Past Roles and Job History (array of inputs)
    $previous_roles = $_POST['previous_role'];
    $previous_companies = $_POST['previous_company'];
    $years_experience = $_POST['years_experience'];

    // Educational Details
    $ol_index_number = $_POST['ol_index_number'];
    $ol_year = $_POST['ol_year'];
    $ol_subjects = $_POST['ol_subjects'];
    $ol_results = $_POST['ol_results'];

    $al_index_number = $_POST['al_index_number'];
    $al_year = $_POST['al_year'];
    $al_subjects = $_POST['al_subjects'];
    $al_results = $_POST['al_results'];

    // Role and Credentials (position)
    $position = isset($_POST['position']) ? htmlspecialchars(trim($_POST['position'])) : null;

    if (!$position) {
        die("Position is required.");
    }

    // Login credentials
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);  // Password encryption

    // Profile Image upload
    $profile_image = $_FILES['profile_image']['name'];
    $target_dir = "Uploads/";
    $target_file = $target_dir . basename($profile_image);
    move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file);

    // Start transaction to ensure atomicity
    $conn->begin_transaction();

    try {
        // 1. 
        $role = 'Staff'; 
        $sql_user = "INSERT INTO user (username, role, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_user);
        $stmt->bind_param('sss', $username, $role, $password);
        $stmt->execute();

        // 2. Insert into the staff table (personal details)
        $sql_staff = "INSERT INTO staff (username, full_name, gender, date_of_birth, postal_address, ethnicity, nic_number, marital_status, whatsapp_number, residence_number, first_language, profile_image, position) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_staff);
        $stmt->bind_param('sssssssssssss', $username, $full_name, $gender, $date_of_birth, $postal_address, $ethnicity, $nic_number, $marital_status, $whatsapp_number, $residence_number, $first_language, $profile_image, $position);
        $stmt->execute();

        // Insert into educational_details table with prepared statement
        $sql_educational_details = "INSERT INTO educational_details (username, other_educational_qualification, professional_qualification, extra_curricular_activities, work_experience) 
        VALUES (?, ?, ?, ?, ?)";
        $stmt_educational_details = $conn->prepare($sql_educational_details);
        $stmt_educational_details->bind_param('sssss', $username, $other_educational_qualification, $professional_qualification, $extra_curricular_activities, $work_experience);
        $stmt_educational_details->execute();

        // 3. Insert past roles and job history
        foreach ($previous_roles as $index => $role) {
            $sql_previous_info = "INSERT INTO previous_info (username, previous_role, previous_company, years_experience) 
                                  VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_previous_info);
            $stmt->bind_param('sssi', $username, $previous_roles[$index], $previous_companies[$index], $years_experience[$index]);
            $stmt->execute();
        }

        // 4. Insert OL results with index_number and year
        for ($i = 0; $i < count($ol_subjects); $i++) {
            $sql_ol_results = "INSERT INTO ol_result_staff (username, subject_name, result, index_number, year) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_ol_results);
            $stmt->bind_param('sssss', $username, $ol_subjects[$i], $ol_results[$i], $ol_index_number, $ol_year);
            $stmt->execute();
        }

        // 5. Insert AL results with index_number and year
        for ($i = 0; $i < count($al_subjects); $i++) {
            $sql_al_results = "INSERT INTO al_result_staff (username, subject_name, result, index_number, year) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_al_results);
            $stmt->bind_param('sssss', $username, $al_subjects[$i], $al_results[$i], $al_index_number, $al_year);
            $stmt->execute();
        }

        // Commit the transaction
        $conn->commit();
        echo "<script>
        alert('Staff successfully added!');
        window.location.href = 'add_staff.php';
    </script>";
    } catch (Exception $e) {
        // If there is any error, rollback the transaction
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Staff</title>
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
        input:focus, select:focus, textarea:focus { animation: glow 1s ease-in-out infinite alternate; }
        /* Label animation */
        .form-group label {
            transition: all 0.3s ease;
            background: white;
            padding: 0 4px;
            line-height: 1;
        }
        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label,
        .form-group select:focus + label,
        .form-group select:not(:placeholder-shown) + label,
        .form-group textarea:focus + label,
        .form-group textarea:not(:placeholder-shown) + label {
            transform: translateY(-2.2rem) scale(0.9);
            color: #eab308;
        }
        /* Shake effect for invalid input */
        .shake { animation: shake 0.3s ease-in-out; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
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
        /* Pulse animation for button */
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Sidebar -->
    <?php include('navbar.php'); ?>


    <!-- Notifications -->
    <?php if (isset($message) && $message): ?>
        <div id="<?php echo isset($messageType) && $messageType === 'success' ? 'successNotification' : 'errorNotification'; ?>" 
             class="notification fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-gradient-to-r 
             <?php echo isset($messageType) && $messageType === 'success' ? 'from-green-500 to-green-600' : 'from-red-500 to-red-600'; ?> 
             text-white px-6 py-3 rounded-md shadow-lg text-sm">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="ml-[68px] p-6">
        <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 animate-bounceIn">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Add New Staff</h2>
            <form action="#" method="POST" enctype="multipart/form-data" id="staffForm">
                <fieldset class="border border-gray-300 rounded-md p-6 mb-6 animate-bounceIn">
                    <legend class="text-lg font-semibold text-yellow-700 px-2">Personal Details</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group relative">
                            <input type="text" id="full_name" name="full_name" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                            <label for="full_name" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Full Name</label>
                        </div>
                        <div class="form-group relative">
                            <select id="gender" name="gender" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer">
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <label for="gender" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Gender</label>
                        </div>
                        <div class="form-group relative">
                            <input type="date" id="date_of_birth" name="date_of_birth" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                            <label for="date_of_birth" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Date of Birth</label>
                        </div>
                        <div class="form-group relative">
                            <textarea id="postal_address" name="postal_address" rows="3" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" "></textarea>
                            <label for="postal_address" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Postal Address</label>
                        </div>
                        <div class="form-group relative">
                            <input type="text" id="ethnicity" name="ethnicity" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                            <label for="ethnicity" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Ethnicity</label>
                        </div>
                        <div class="form-group relative">
                            <input type="text" id="nic_number" name="nic_number" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                            <label for="nic_number" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">NIC Number</label>
                        </div>
                        <div class="form-group relative">
                            <select id="marital_status" name="marital_status" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer">
                                <option value="" disabled selected>Select Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                            </select>
                            <label for="marital_status" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Marital Status</label>
                        </div>
                        <div class="form-group relative">
                            <input type="tel" id="whatsapp_number" name="whatsapp_number" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder="">
                            <label for="whatsapp_number" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">WhatsApp Number</label>
                        </div>
                        <div class="form-group relative">
                            <input type="tel" id="residence_number" name="residence_number" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder="">
                            <label for="residence_number" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Residence Number</label>
                        </div>
                        <div class="form-group relative">
                            <input type="text" id="first_language" name="first_language" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                            <label for="first_language" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">First Language</label>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border border-gray-300 rounded-md p-6 mb-6 animate-bounceIn">
                    <legend class="text-lg font-semibold text-yellow-700 px-2">Other Educational Details</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group relative">
                            <textarea id="other_educational_qualification" name="other_educational_qualification" rows="3" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" "></textarea>
                            <label for="other_educational_qualification" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Other Educational Qualification</label>
                        </div>
                        <div class="form-group relative">
                            <textarea id="professional_qualification" name="professional_qualification" rows="3" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" "></textarea>
                            <label for="professional_qualification" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Professional Qualification</label>
                        </div>
                        <div class="form-group relative">
                            <textarea id="extra_curricular_activities" name="extra_curricular_activities" rows="3" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" "></textarea>
                            <label for="extra_curricular_activities" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Extra Curricular Activities</label>
                        </div>
                        <div class="form-group relative">
                            <textarea id="work_experience" name="work_experience" rows="3" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" "></textarea>
                            <label for="work_experience" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Work Experience</label>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border border-gray-300 rounded-md p-6 mb-6 animate-bounceIn">
                    <legend class="text-lg font-semibold text-yellow-700 px-2">Past Roles and Job History</legend>
                    <div id="previous_roles_container" class="space-y-4">
                        <div class="previous_role_entry bg-gray-50 p-4 rounded-md">
                            <div class="space-y-4">
                                <div class="form-group relative">
                                    <input type="text" name="previous_role[]" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                                    <label class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Previous Role</label>
                                </div>
                                <div class="form-group relative">
                                    <input type="text" name="previous_company[]" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                                    <label class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Previous Company/Organization</label>
                                </div>
                                <div class="form-group relative">
                                    <input type="number" name="years_experience[]" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                                    <label class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Total Years of Experience</label>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button type="button" onclick="removeRole(this)" class="p-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-200 btn-gradient">Remove</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="addMoreRoles()" class="mt-4 p-3 bg-gradient-to-r from-yellow-900 to-yellow-700 text-white rounded-md btn-gradient hover:bg-yellow-800 transition duration-200 hover-loop">Add More</button>
                </fieldset>

                <fieldset class="border border-gray-300 rounded-md p-6 mb-6 animate-bounceIn">
                    <legend class="text-lg font-semibold text-yellow-700 px-2">Educational Details</legend>
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-4">GCE O/L</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-group relative">
                                    <input type="text" id="ol_index_number" name="ol_index_number" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                                    <label for="ol_index_number" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Index Number</label>
                                </div>
                                <div class="form-group relative">
                                    <input type="number" id="ol_year" name="ol_year" min="1900" max="2099" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                                    <label for="ol_year" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Year</label>
                                </div>
                            </div>
                            <h4 class="text-md font-medium text-gray-800 mt-4 mb-2">Subjects and Results</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php for ($i = 0; $i < 9; $i++) { ?>
                                    <div class="subject-group flex space-x-4">
                                        <div class="form-group relative flex-1">
                                            <input type="text" name="ol_subjects[]" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                                            <label class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Subject</label>
                                        </div>
                                        <div class="form-group relative flex-1">
                                            <select name="ol_results[]" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer">
                                                <option value="" disabled selected>Select Result</option>
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="C">C</option>
                                                <option value="S">S</option>
                                                <option value="W">W</option>
                                            </select>
                                            <label class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Result</label>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-800 mb-4">GCE A/L</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="form-group relative">
                                    <input type="text" id="al_index_number" name="al_index_number" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                                    <label for="al_index_number" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Index Number</label>
                                </div>
                                <div class="form-group relative">
                                    <input type="number" id="al_year" name="al_year" min="1900" max="2099" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                                    <label for="al_year" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Year</label>
                                </div>
                            </div>
                            <h4 class="text-md font-medium text-gray-800 mt-4 mb-2">Subjects and Results</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php for ($i = 0; $i < 4; $i++) { ?>
                                    <div class="subject-group flex space-x-4">
                                        <div class="form-group relative flex-1">
                                            <input type="text" name="al_subjects[]" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                                            <label class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Subject</label>
                                        </div>
                                        <div class="form-group relative flex-1">
                                            <select name="al_results[]" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer">
                                                <option value="" disabled selected>Select Result</option>
                                                <option value="A">A</option>
                                                <option value="B">B</option>
                                                <option value="C">C</option>
                                                <option value="S">S</option>
                                                <option value="W">W</option>
                                            </select>
                                            <label class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Result</label>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border border-gray-300 rounded-md p-6 mb-6 animate-bounceIn">
                    <legend class="text-lg font-semibold text-yellow-700 px-2">Role and Credentials</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group relative">
                            <input type="text" id="position" name="position" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" " required>
                            <label for="position" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Position</label>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border border-gray-300 rounded-md p-6 mb-6 animate-bounceIn">
                    <legend class="text-lg font-semibold text-yellow-700 px-2">Login Credentials</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="form-group relative">
                            <input type="text" id="username" name="username" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                            <label for="username" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Username</label>
                        </div>
                        <div class="form-group relative">
                            <input type="password" id="password" name="password" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                            <label for="password" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Password</label>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="border border-gray-300 rounded-md p-6 mb-6 animate-bounceIn">
                    <legend class="text-lg font-semibold text-yellow-700 px-2">Profile Image</legend>
                    <div class="form-group">
                        <label for="profile_image" class="block text-sm font-medium text-gray-700 mb-2">Upload Profile Image</label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200">
                    </div>
                </fieldset>

                <button type="submit" class="w-full p-3 bg-gradient-to-r from-yellow-900 to-yellow-700 text-white rounded-md btn-gradient hover:bg-yellow-800 transition duration-200 hover-loop">Submit</button>
            </form>
        </div>
    </main>

    <script>
        // Auto-hide notifications after 2.5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 2500);
            });
        });

        // Function to dynamically add more past role input fields
        function addMoreRoles() {
            const container = document.getElementById('previous_roles_container');
            const newEntry = document.createElement('div');
            newEntry.className = 'previous_role_entry bg-gray-50 p-4 rounded-md';
            newEntry.innerHTML = `
                <div class="space-y-4">
                    <div class="form-group relative">
                        <input type="text" name="previous_role[]" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                        <label class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Previous Role</label>
                    </div>
                    <div class="form-group relative">
                        <input type="text" name="previous_company[]" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                        <label class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Previous Company/Organization</label>
                    </div>
                    <div class="form-group relative">
                        <input type="number" name="years_experience[]" class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                        <label class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Total Years of Experience</label>
                    </div>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="button" onclick="removeRole(this)" class="p-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-200 btn-gradient">Remove</button>
                </div>
            `;
            container.appendChild(newEntry);
        }

        // Function to remove a specific role entry
        function removeRole(button) {
            button.parentElement.parentElement.remove();
        }
    </script>
</body>
</html>
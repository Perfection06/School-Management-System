<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Database connection
include("db_connection.php");

$errorMessage = $successMessage = "";

// Function to update credentials
function updateAdminCredentials($currentPassword, $newUsername, $newPassword, $pdo) {
    $adminUsername = $_SESSION['user']['username'];  // Get admin username from session

    // Fetch the current admin record using username
    $stmt = $pdo->prepare("SELECT * FROM Admin WHERE username = :username");
    $stmt->execute(['username' => $adminUsername]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify current password
    if ($admin && password_verify($currentPassword, $admin['password'])) {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the sender_username in messages before updating the admin
        $updateMessagesStmt = $pdo->prepare("UPDATE messages SET sender_username = :newUsername WHERE sender_username = :currentUsername");
        $updateMessagesStmt->execute([
            'newUsername' => $newUsername,
            'currentUsername' => $adminUsername
        ]);

        // Update the credentials in admin table using the admin's id
        $updateAdminStmt = $pdo->prepare("UPDATE Admin SET username = :newUsername, password = :newPassword WHERE username = :currentUsername");
        $updateAdminStmt->execute([
            'newUsername' => $newUsername,
            'newPassword' => $hashedPassword,
            'currentUsername' => $adminUsername // Ensure you're using the current username in the WHERE clause
        ]);

        // Update the session with the new username
        $_SESSION['user']['username'] = $newUsername;

        return "Credentials updated successfully.";
    } else {
        return "Current password is incorrect.";
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'];
    $newUsername = $_POST['new_username'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword === $confirmPassword) {
        $successMessage = updateAdminCredentials($currentPassword, $newUsername, $newPassword, $pdo);
    } else {
        $errorMessage = "New password and confirm password do not match.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Change Credentials</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        /* Form container animations */
        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.8) rotate(-2deg); }
            60% { opacity: 1; transform: scale(1.05) rotate(1deg); }
            100% { transform: scale(1) rotate(0deg); }
        }
        .animate-bounceIn { animation: bounceIn 0.6s ease-out forwards; }

        /* Typing animation for input text */
        input:focus { animation: glow 1s ease-in-out infinite alternate; }
        @keyframes glow {
            from { box-shadow: 0 0 5px rgba(234, 179, 8, 0.5); }
            to { box-shadow: 0 0 15px rgba(234, 179, 8, 0.8); }
        }

        /* Label animation on focus */
        .form-group label {
            transition: all 0.3s ease;
            background: white;
            padding: 0 4px;
            line-height: 1;
        }
        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label {
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
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <!-- Sidebar -->
    <?php include('navbar.php'); ?>

    <!-- Form Container -->
    <div class="ml-[68px] w-full max-w-md p-8 bg-white rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:rotate-1 animate-bounceIn">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Change Credentials</h2>
        <form method="post" action="" id="credentialsForm">
            <div class="form-group mb-4 relative">
                <input type="password" id="current_password" name="current_password" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                <label for="current_password" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Current Password</label>
            </div>

            <div class="form-group mb-4 relative">
                <input type="text" id="new_username" name="new_username" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                <label for="new_username" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">New Username</label>
            </div>

            <div class="form-group mb-4 relative">
                <input type="password" id="new_password" name="new_password" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                <label for="new_password" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">New Password</label>
            </div>

            <div class="form-group mb-6 relative">
                <input type="password" id="confirm_password" name="confirm_password" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                <label for="confirm_password" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Confirm New Password</label>
            </div>

            <button type="submit" class="w-full p-3 bg-gradient-to-r from-yellow-900 to-yellow-700 text-white rounded-md btn-gradient hover:bg-yellow-800 transition duration-200 hover-loop">Update Credentials</button>

            <?php if (isset($errorMessage)): ?>
                <p class="text-red-500 text-sm mt-4 text-center shake"><?php echo htmlspecialchars($errorMessage); ?></p>
            <?php endif; ?>
            
            <?php if (isset($successMessage)): ?>
                <p class="text-green-500 text-sm mt-4 text-center animate-bounceIn"><?php echo htmlspecialchars($successMessage); ?></p>
            <?php endif; ?>
        </form>
    </div>

    <script>
        // Add shake effect on invalid form submission
        document.getElementById('credentialsForm').addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input');
            let valid = true;
            inputs.forEach(input => {
                if (!input.value) {
                    input.classList.add('shake', 'border-red-500');
                    valid = false;
                    setTimeout(() => input.classList.remove('shake'), 300);
                }
            });
            if (!valid) e.preventDefault();
        });

        // Remove shake effect after input
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        });
    </script>
</body>
</html>
<?php
session_start();

// Include database connection
include('db_connection.php');

// Initialize error message
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check credentials based on roles
    $user = null;

    // Check in the Admin table
    $sqlAdmin = "SELECT * FROM admin WHERE username = ?";
    $stmtAdmin = $pdo->prepare($sqlAdmin);
    $stmtAdmin->execute([$username]);
    $admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        // Store admin details in session
        $user = [
            'username' => $admin['username'],
            'role' => 'Admin'
        ];
        $_SESSION['user'] = $user;

        // Redirect to Admin Dashboard
        header("Location: Admin_Dashboard.php");
        exit;
    }

    // Check in the User table
    $sqlUser = "SELECT * FROM user WHERE username = ? AND active = 1";
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([$username]);
    $userDetails = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if ($userDetails && password_verify($password, $userDetails['password'])) {
        // Store user details in session
        $user = [
            'username' => $userDetails['username'],
            'role' => $userDetails['role']
        ];
        $_SESSION['user'] = $user;

        // Redirect based on role
        switch ($userDetails['role']) {
            case 'Teacher':
                header("Location: ./Teachers/Teacher_Dashboard.php");
                break;
            case 'NoClass_Teacher':
                header("Location: ./Sub Teacher/Sub_Teacher_Dashboard.php");
                break;
            case 'Staff':
                header("Location: ./Staffs/Staff_Dashboard.php");
                break;
            default:
                $errorMessage = "Unauthorized role!";
                session_destroy();
        }
        exit;
    }

    // Check in the Students table
    $sqlStudent = "SELECT * FROM Students WHERE username = ?";
    $stmtStudent = $pdo->prepare($sqlStudent);
    $stmtStudent->execute([$username]);
    $student = $stmtStudent->fetch(PDO::FETCH_ASSOC);

    if ($student && password_verify($password, $student['password'])) {
        // Store student details in session
        $user = [
            'username' => $student['username'],
            'role' => 'Student'
        ];
        $_SESSION['user'] = $user;

        // Redirect to Student Dashboard
        header("Location: ./Students/Student_Dashboard.php");
        exit;
    }

    // Check in the Accountant table
    $sqlAccountant = "SELECT * FROM accountant WHERE username = ?";
    $stmtAccountant = $pdo->prepare($sqlAccountant);
    $stmtAccountant->execute([$username]);
    $accountant = $stmtAccountant->fetch(PDO::FETCH_ASSOC);

    if ($accountant && password_verify($password, $accountant['password'])) {
        // Store accountant details in session
        $user = [
            'username' => $accountant['username'],
            'role' => 'Accountant'
        ];
        $_SESSION['user'] = $user;

        // Redirect to Accountant Dashboard
        header("Location: ./Cashier/Cashier_Dashboard.php");
        exit;
    }

    // If no match found
    $errorMessage = "Invalid username or password!";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        /* Background floating icons animation */
        .background-icons {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        .floating-icon {
            position: absolute;
            color: rgba(234, 179, 8, 0.5);
            font-size: 3rem;
            animation: floatAnimation 6s linear infinite;
            will-change: transform, opacity;
        }
        .floating-icon:nth-child(1) { left: 5%; animation-duration: 6s; animation-delay: 0s; }
        .floating-icon:nth-child(2) { left: 15%; animation-duration: 7s; animation-delay: 1s; }
        .floating-icon:nth-child(3) { left: 25%; animation-duration: 6.5s; animation-delay: 2s; }
        .floating-icon:nth-child(4) { left: 40%; animation-duration: 7.5s; animation-delay: 3s; }
        .floating-icon:nth-child(5) { left: 55%; animation-duration: 6s; animation-delay: 4s; }
        .floating-icon:nth-child(6) { left: 70%; animation-duration: 7s; animation-delay: 0.5s; }
        .floating-icon:nth-child(7) { left: 85%; animation-duration: 6.5s; animation-delay: 1.5s; }
        .floating-icon:nth-child(8) { left: 95%; animation-duration: 7.5s; animation-delay: 2.5s; }
        @keyframes floatAnimation {
            0% { 
                transform: translateY(100vh) translateX(0) rotate(0deg); 
                opacity: 0; 
            }
            20% { 
                opacity: 0.7; 
            }
            80% { 
                opacity: 0.7; 
            }
            100% { 
                transform: translateY(-100vh) translateX(50px) rotate(5deg); 
                opacity: 0; 
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .floating-icon {
                animation: none;
                display: none;
            }
        }

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

        /* Eye icon for password visibility */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #aaa;
            transition: color 0.3s;
        }
        .password-toggle:hover { color: #eab308; }

        /* Notification animation */
        @keyframes slideDownFadeOut {
            0% { opacity: 0; transform: translateY(-20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        .notification {
            animation: slideDownFadeOut 2.5s ease-in-out forwards;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center">
    <!-- Background Animation -->
    <div class="background-icons">
        <i class="bx bx-book floating-icon" aria-hidden="true"></i>
        <i class="bx bx-pencil floating-icon" aria-hidden="true"></i>
        <i class="bx bx-graduation floating-icon" aria-hidden="true"></i>
        <i class="bx bx-book-open floating-icon" aria-hidden="true"></i>
        <i class="bx bx-ruler floating-icon" aria-hidden="true"></i>
        <i class="bx bx-calculator floating-icon" aria-hidden="true"></i>
        <i class="bx bx-chalkboard floating-icon" aria-hidden="true"></i>
        <i class="bx bx-bus-school floating-icon" aria-hidden="true"></i>
    </div>

    <!-- Header -->
    <header class="bg-gradient-to-r from-yellow-900 to-yellow-700 text-white py-4 px-6 shadow-md w-full text-center fixed top-0 z-10">
        <h1 class="text-2xl font-bold">School Management System</h1>
    </header>

    <!-- Form Container -->
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 hover:rotate-1 animate-bounceIn mt-20">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Login</h2>
        <form method="post" action="login.php" id="loginForm">
            <div class="form-group mb-4 relative">
                <input type="text" id="username" name="username" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                <label for="username" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Username</label>
            </div>

            <div class="form-group mb-4 relative">
                <input type="password" id="password" name="password" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                <label for="password" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Password</label>
                <i class="bx bx-show password-toggle" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility"></i>
            </div>

            <div class="flex items-center mb-6">
                <input type="checkbox" id="remember" name="remember" class="mr-2">
                <label for="remember" class="text-sm text-gray-700">Remember Me</label>
            </div>

            <button type="submit" class="w-full p-3 bg-gradient-to-r from-yellow-900 to-yellow-700 text-white rounded-md btn-gradient hover:bg-yellow-800 transition duration-200 hover-loop">Login</button>

            <a href="forgot_password.php" class="block mt-4 text-sm text-yellow-700 hover:underline text-center">Forgot Password?</a>

            <?php if (isset($errorMessage)): ?>
                <p class="text-red-500 text-sm mt-4 text-center shake notification"><?php echo htmlspecialchars($errorMessage); ?></p>
            <?php endif; ?>
        </form>
    </div>

    <script>
        // Toggle password visibility
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bx-show');
                toggleIcon.classList.add('bx-hide');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bx-hide');
                toggleIcon.classList.add('bx-show');
            }
        }

        // Add shake effect on invalid form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[required]');
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

        // Auto-hide notifications after 2.5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 2500);
            });
        });
    </script>
</body>
</html>
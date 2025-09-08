<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Get admin username from session
$username = htmlspecialchars($_SESSION['user']['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        /* Additional animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn { animation: fadeIn 0.5s ease-out forwards; }
        .hover-loop { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-gradient-to-r from-yellow-900 to-yellow-700 text-white py-4 px-6 shadow-md ml-[68 \

px] flex items-center">
        <div class="flex-1 text-center">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
        </div>
    </header>

    <!-- Sidebar (assuming navbar.php is the sidebar) -->
    <?php include('navbar.php'); ?>

    <!-- Main Content -->
    <main class="ml-[68px] p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Widgets go here with animations and effects -->
        <?php include 'user_widget.php'; ?>
        <?php include 'generate_daily_report.php'; ?>
        <?php include 'event_widget.php'; ?>
        <?php include 'results_widget.php'; ?>
    </main>

    <script src="assets/js/main.js"></script>
    <?php include('notification.php'); ?>
</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}
$username = htmlspecialchars($_SESSION['user']['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
  <style>
    @keyframes slide-down {
        from { transform: translateY(-20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .header-animate {
        animation: slide-down 0.5s ease-in-out;
    }
  </style>
</head>
<body class="bg-gray-50 font-sans">
  <!-- Header -->
  <header class="fixed top-0 left-0 w-full bg-white shadow-md z-40 header-animate">
      <div class="flex items-center justify-center py-4 px-8">
          <h1 class="text-xl font-bold text-indigo-600">Welcome, <?= $username ?></h1>
      </div>
  </header>

  <!-- Sidebar -->
  <div class="fixed top-[60px] left-0 h-full z-50">
      <?php include('navbar.php'); ?>
  </div>

  <!-- Main Content -->
  <main class="ml-[80px] mt-[80px] p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
      <?php include('attendance_widget.php'); ?>
      <?php include('fee_widget.php'); ?>
  </main>

  <!-- Notifications -->
  <?php include('notification.php'); ?>
</body>
</html>
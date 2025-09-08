<?php
  // Detect current page
  $current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Sidebar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; background-color: #f9fafb; }
    .sidebar {
      width: 68px;
      transition: width 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
      background: linear-gradient(to bottom, #4a2c00, #eab308, #4a2c00);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    .sidebar:hover { width: 260px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3); }
    .sidebar .nav-text { opacity: 0; transition: opacity 0.3s ease-in-out; }
    .sidebar:hover .nav-text { opacity: 1; }
    .active-link {
      background-color: rgba(255, 255, 255, 0.25);
      border-radius: 10px;
      animation: pulse 1.5s infinite ease-in-out;
    }
    details[open] summary { background: rgba(255, 255, 255, 0.1); border-radius: 8px; }
    .nav-link { transition: all 0.3s ease-in-out; position: relative; }
    .nav-link:hover {
      transform: translateX(5px);
      background-color: rgba(234, 179, 8, 0.8);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    .dropdown-item { transition: all 0.3s ease-in-out; transform: translateY(10px); opacity: 0; }
    details[open] .dropdown-item { transform: translateY(0); opacity: 1; }
    .chevron { transition: transform 0.3s ease-in-out; }
    .group[open] .chevron { transform: rotate(180deg); animation: bounce 0.3s ease-in-out; }
    .tooltip {
      position: absolute;
      left: 70px;
      background: #333;
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      opacity: 0;
      transition: opacity 0.2s ease-in-out;
      pointer-events: none;
    }
    .nav-link:hover .tooltip, .sidebar:not(:hover) .nav-link .tooltip:hover { opacity: 1; }
    @keyframes pulse {
      0% { background-color: rgba(255, 255, 255, 0.25); }
      50% { background-color: rgba(255, 255, 255, 0.35); }
      100% { background-color: rgba(255, 255, 255, 0.25); }
    }
    @keyframes bounce {
      0%, 100% { transform: rotate(180deg) scale(1); }
      50% { transform: rotate(180deg) scale(1.2); }
    }
    @media (prefers-reduced-motion: reduce) {
      .active-link, .group[open] .chevron, .nav-link:hover, .dropdown-item { animation: none; transform: none; }
    }
    @media screen and (max-width: 320px) {
      .sidebar { width: 50px; }
      .sidebar:hover { width: 220px; }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar fixed top-0 left-0 h-screen shadow-lg z-50 overflow-y-auto">
    <nav class="flex flex-col justify-between h-full py-6 px-3">
      <!-- Logo -->
      <div>
        <a href="#" class="flex items-center mb-8 nav-link" aria-label="Reliance Home">
          <i class="bx bxs-school text-white text-3xl"></i>
          <span class="ml-3 font-semibold text-lg text-white nav-text">School</span>
          <span class="tooltip">Home</span>
        </a>

        <!-- Menu -->
        <ul class="space-y-3 text-white text-sm">
          <h3 class="uppercase text-xs tracking-wider text-gray-300 nav-text px-2">System</h3>

          <!-- Dashboard -->
          <li>
            <a href="./Student_Dashboard.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page == 'Student_Dashboard.php') ? 'active-link' : ''; ?>" aria-label="Dashboard">
              <i class="bx bx-home text-lg"></i>
              <span class="ml-3 nav-text">Dashboard</span>
              <span class="tooltip">Dashboard</span>
            </a>
          </li>

          <!-- Profile -->
          <li>
            <a href="./profile.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page == 'profile.php') ? 'active-link' : ''; ?>" aria-label="Profile">
              <i class="bx bx-user text-lg"></i>
              <span class="ml-3 nav-text">Profile</span>
              <span class="tooltip">Profile</span>
            </a>
          </li>

          <!-- Class Files -->
          <li>
            <a href="./assignment.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page == 'assignment.php') ? 'active-link' : ''; ?>" aria-label="Class Files">
              <i class="bx bx-book text-lg"></i>
              <span class="ml-3 nav-text">Class Files</span>
              <span class="tooltip">Class Files</span>
            </a>
          </li>

          <!-- Notice -->
          <li>
            <a href="./notice.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page == 'notice.php') ? 'active-link' : ''; ?>" aria-label="Notice">
              <i class="bx bx-bell text-lg"></i>
              <span class="ml-3 nav-text">Notice</span>
              <span class="tooltip">Notice</span>
            </a>
          </li>

          <!-- Event -->
          <li>
            <a href="./events.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page == 'events.php') ? 'active-link' : ''; ?>" aria-label="Event">
              <i class="bx bx-calendar-event text-lg"></i>
              <span class="ml-3 nav-text">Event</span>
              <span class="tooltip">Event</span>
            </a>
          </li>

          <!-- Attendance -->
          <li>
            <a href="./attendance.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page == 'attendance.php') ? 'active-link' : ''; ?>" aria-label="Attendance">
              <i class="bx bx-check-circle text-lg"></i>
              <span class="ml-3 nav-text">Attendance</span>
              <span class="tooltip">Attendance</span>
            </a>
          </li>

          <!-- Dropdown: Messages -->
          <li>
            <details class="group" <?php if (in_array($current_page, ['message.php', 'view_messages.php'])) echo 'open'; ?>>
              <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition" aria-label="Messages">
                <i class="bx bx-message-square-detail text-lg"></i>
                <span class="ml-3 nav-text">Messages</span>
                <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
                <span class="tooltip">Messages</span>
              </summary>
              <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
                <li><a href="./message.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'message.php') ? 'active-link text-white' : ''; ?>">Send Message</a></li>
                <li><a href="./view_messages.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'view_messages.php') ? 'active-link text-white' : ''; ?>">View Message</a></li>
              </ul>
            </details>
          </li>

          <!-- Dropdown: Exam -->
          <li>
            <details class="group" <?php if (in_array($current_page, ['exam_timetable.php', 'view_result.php', 'test_results.php'])) echo 'open'; ?>>
              <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition" aria-label="Exam">
                <i class="bx bxs-graduation text-lg"></i>
                <span class="ml-3 nav-text">Exam</span>
                <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
                <span class="tooltip">Exam</span>
              </summary>
              <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
                <li><a href="./exam_timetable.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'exam_timetable.php') ? 'active-link text-white' : ''; ?>">Exam Timetable</a></li>
                <li><a href="./view_result.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'view_result.php') ? 'active-link text-white' : ''; ?>">Results</a></li>
                <li><a href="./test_results.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'test_results.php') ? 'active-link text-white' : ''; ?>">Test Results</a></li>
              </ul>
            </details>
          </li>

          <!-- Dropdown: Feedback -->
          <li>
            <details class="group" <?php if (in_array($current_page, ['feedback.php', 'subject_feedback.php'])) echo 'open'; ?>>
              <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition" aria-label="Feedback">
                <i class="bx bx-comment-detail text-lg"></i>
                <span class="ml-3 nav-text">Feedback</span>
                <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
                <span class="tooltip">Feedback</span>
              </summary>
              <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
                <li><a href="./feedback.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'feedback.php') ? 'active-link text-white' : ''; ?>">Class Feedback</a></li>
                <li><a href="./subject_feedback.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'subject_feedback.php') ? 'active-link text-white' : ''; ?>">Subject Feedback</a></li>
              </ul>
            </details>
          </li>
        </ul>
      </div>

      <!-- Logout -->
      <div>
        <a href="../login.php" class="nav-link flex items-center px-2 py-2 text-red-400 hover:bg-red-500 hover:text-white rounded-md transition" aria-label="Log Out">
          <i class="bx bx-log-out text-lg"></i>
          <span class="ml-3 nav-text">Log Out</span>
          <span class="tooltip">Log Out</span>
        </a>
      </div>
    </nav>
  </div>
</body>
</html>
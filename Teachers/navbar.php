<?php
  // Detect current page
  $current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>School Sidebar</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
    .sidebar {
      width: 68px;
      transition: width 0.3s ease-in-out;
      overflow: hidden;
      overflow-y: auto;
      background: linear-gradient(to bottom, #4a2c00, #eab308, #4a2c00);
    }
    .sidebar:hover { width: 240px; }
    .sidebar .nav-text {
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
    }
    .sidebar:hover .nav-text { opacity: 1; }
    .active-link {
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      animation: pulse 1.5s infinite;
    }
    details[open] summary { 
      background: rgba(255, 255, 255, 0.08); 
      border-radius: 6px; 
    }
    details { 
      transition: all 0.3s ease-in-out; 
    }
    .nav-link {
      transition: all 0.2s ease-in-out;
    }
    .nav-link:hover {
      transform: scale(1.05);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      background-color: rgba(234, 179, 8, 0.7);
    }
    .dropdown-item {
      transition: all 0.2s ease-in-out;
    }
    .dropdown-item:hover {
      transform: translateX(5px);
      background-color: rgba(234, 179, 8, 0.7);
    }
    .chevron {
      transition: transform 0.3s ease-in-out;
    }
    .group[open] .chevron {
      transform: rotate(180deg) scale(1.1);
      animation: bounce 0.3s ease-in-out;
    }
    @keyframes pulse {
      0% { background-color: rgba(255, 255, 255, 0.2); }
      50% { background-color: rgba(255, 255, 255, 0.3); }
      100% { background-color: rgba(255, 255, 255, 0.2); }
    }
    @keyframes bounce {
      0%, 100% { transform: rotate(180deg) scale(1); }
      50% { transform: rotate(180deg) scale(1.2); }
    }
    @media (prefers-reduced-motion: reduce) {
      .active-link,
      .group[open] .chevron,
      .nav-link:hover,
      .dropdown-item:hover {
        animation: none;
        transform: none;
      }
    }
    @media screen and (max-width: 320px) {
      .sidebar {
        width: 50px;
      }
      .sidebar:hover {
        width: 200px;
      }
    }
  </style>
</head>
<body class="bg-gray-100">
  <!-- Sidebar -->
  <div class="sidebar fixed top-0 left-0 h-screen shadow-lg z-50 overflow-y-auto">
    <nav class="flex flex-col justify-between h-full py-6 px-3">
      <!-- Logo -->
      <div>
        <a href="#" class="flex items-center mb-8 nav-link">
          <i class="bx bxs-school text-white text-3xl"></i>
          <span class="ml-3 font-semibold text-lg text-white nav-text">School</span>
        </a>

        <!-- Menu -->
        <ul class="space-y-3 text-white text-sm">
          <h3 class="uppercase text-xs tracking-wider text-gray-300 nav-text px-2">System</h3>

          <!-- Dashboard -->
          <li>
            <a href="./Teacher_Dashboard.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page == 'Teacher_Dashboard.php') ? 'active-link' : ''; ?>">
              <i class="bx bx-home text-lg"></i>
              <span class="ml-3 nav-text">Dashboard</span>
            </a>
          </li>

          <!-- Profile -->
          <li>
            <a href="./profile.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page == 'profile.php') ? 'active-link' : ''; ?>">
              <i class="bx bx-user text-lg"></i>
              <span class="ml-3 nav-text">Profile</span>
            </a>
          </li>

          <!-- Dropdown: Attendance -->
          <li>
            <details class="group" <?php if (in_array($current_page, ['attendance.php', 'attendance_history.php', 'generate_report.php'])) echo 'open'; ?>>
              <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition" aria-label="Toggle Attendance dropdown">
                <i class="bx bx-user text-lg"></i>
                <span class="ml-3 nav-text">Attendance</span>
                <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
              </summary>
              <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
                <li><a href="./attendance.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'attendance.php') ? 'active-link text-white' : ''; ?>">Mark Attendance</a></li>
                <li><a href="./attendance_history.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'attendance_history.php') ? 'active-link text-white' : ''; ?>">Attendance Records</a></li>
                <li><a href="./generate_report.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'generate_report.php') ? 'active-link text-white' : ''; ?>">Attendance Report</a></li>
              </ul>
            </details>
          </li>

          <!-- Dropdown: Exams -->
          <li>
            <details class="group" <?php if (in_array($current_page, ['tests.php', 'view_exam_timetable.php', 'test_marks.php', 'marks.php', 'rank.php', 'chapters.php'])) echo 'open'; ?>>
              <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition" aria-label="Toggle Exams dropdown">
                <i class="bx bxs-graduation text-lg"></i>
                <span class="ml-3 nav-text">Exams</span>
                <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
              </summary>
              <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
                <li><a href="./tests.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'tests.php') ? 'active-link text-white' : ''; ?>">Create Test</a></li>
                <li><a href="./view_exam_timetable.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'view_exam_timetable.php') ? 'active-link text-white' : ''; ?>">Exam Timetable</a></li>
                <li><a href="./test_marks.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'test_marks.php') ? 'active-link text-white' : ''; ?>">Test Marks Update</a></li>
                <li><a href="./marks.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'marks.php') ? 'active-link text-white' : ''; ?>">Update Marks</a></li>
                <li><a href="./rank.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'rank.php') ? 'active-link text-white' : ''; ?>">Results</a></li>
                <li><a href="./chapters.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'chapters.php') ? 'active-link text-white' : ''; ?>">Chapters</a></li>
              </ul>
            </details>
          </li>

          <!-- Dropdown: Message -->
          <li>
            <details class="group" <?php if (in_array($current_page, ['message.php', 'view_messages.php'])) echo 'open'; ?>>
              <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition" aria-label="Toggle Message dropdown">
                <i class="bx bx-message-square-detail text-lg"></i>
                <span class="ml-3 nav-text">Message</span>
                <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
              </summary>
              <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
                <li><a href="./message.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'message.php') ? 'active-link text-white' : ''; ?>">Send Message</a></li>
                <li><a href="./view_messages.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'view_messages.php') ? 'active-link text-white' : ''; ?>">View Message</a></li>
              </ul>
            </details>
          </li>

          <!-- Dropdown: Feedback -->
          <li>
            <details class="group" <?php if (in_array($current_page, ['feedback.php', 'subject_feedback.php'])) echo 'open'; ?>>
              <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition" aria-label="Toggle Feedback dropdown">
                <i class="bx bx-comment-detail text-lg"></i>
                <span class="ml-3 nav-text">Feedback</span>
                <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
              </summary>
              <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
                <li><a href="./feedback.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'feedback.php') ? 'active-link text-white' : ''; ?>">Class Feedback</a></li>
                <li><a href="./subject_feedback.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page == 'subject_feedback.php') ? 'active-link text-white' : ''; ?>">Subject Feedback</a></li>
              </ul>
            </details>
          </li>

          <!-- Uploads -->
          <li>
            <a href="./assignment.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page == 'assignment.php') ? 'active-link' : ''; ?>">
              <i class="bx bx-upload text-lg"></i>
              <span class="ml-3 nav-text">Uploads</span>
            </a>
          </li>

          <!-- Notice -->
          <li>
            <a href="./notice.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page == 'notice.php') ? 'active-link' : ''; ?>">
              <i class="bx bx-bell text-lg"></i>
              <span class="ml-3 nav-text">Notice</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- Logout -->
      <div>
        <a href="../login.php" class="nav-link flex items-center px-2 py-2 text-red-400 hover:bg-red-500 hover:text-white rounded-md transition" aria-label="Log out">
          <i class="bx bx-log-out text-lg"></i>
          <span class="ml-3 nav-text">Log Out</span>
        </a>
      </div>
    </nav>
  </div>
</body>
</html>
<?php
  // Detect current page
  $current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
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
    }
    .sidebar:hover { width: 240px; }
    .sidebar .nav-text {
      opacity: 0;
     ritu: 0.3s ease-in-out;
      transition: opacity 0.3s ease-in-out;
    }
    .sidebar:hover .nav-text { opacity: 1; }
    .active-link {
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      animation: pulse 1.5s infinite;
    }
    details[open] summary { 
      background: rgba(255,255,255,0.08); 
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
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
  </style>
</head>
<body class="bg-gray-100">

<!-- Sidebar -->
<div class="sidebar fixed top-0 left-0 h-screen bg-gradient-to-b from-yellow-900 via-yellow-800 to-yellow-700 shadow-lg z-50 overflow-y-auto">
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
          <a href="./Admin_Dashboard.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page=='Admin_Dashboard.php')?'active-link':''; ?>">
            <i class="bx bxs-dashboard text-lg"></i>
            <span class="ml-3 nav-text">Dashboard</span>
          </a>
        </li>

        <!-- Admin Settings -->
        <li>
          <a href="./Admin.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page=='Admin.php')?'active-link':''; ?>">
            <i class="bx bx-cog text-lg"></i>
            <span class="ml-3 nav-text">Change Credentials</span>
          </a>
        </li>

        <!-- Gallery -->
        <li>
          <a href="./gallery1.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page=='gallery1.php')?'active-link':''; ?>">
            <i class="bx bx-image text-lg"></i>
            <span class="ml-3 nav-text">Gallery</span>
          </a>
        </li>

        <!-- Dropdown: User -->
        <li>
          <details class="group" <?php if(in_array($current_page, ['add_teacher.php','add_sub_teacher.php','add_staff.php','add_accountant.php','view_teachers_staff.php','blocks.php'])) echo 'open'; ?>>
            <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition">
              <i class="bx bx-user-circle text-lg"></i>
              <span class="ml-3 nav-text">User</span>
              <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
            </summary>
            <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
              <li><a href="./add_teacher.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='add_teacher.php')?'active-link text-white':''; ?>">Add Teachers</a></li>
              <li><a href="./add_sub_teacher.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='add_sub_teacher.php')?'active-link text-white':''; ?>">Add Sub Teachers</a></li>
              <li><a href="./add_staff.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='add_staff.php')?'active-link text-white':''; ?>">Add Staff</a></li>
              <li><a href="./add_accountant.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='add_accountant.php')?'active-link text-white':''; ?>">Add Cashier</a></li>
              <li><a href="./view_teachers_staff.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='view_teachers_staff.php')?'active-link text-white':''; ?>">View Users</a></li>
              <li><a href="./blocks.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='blocks.php')?'active-link text-white':''; ?>">Blocked Users</a></li>
            </ul>
          </details>
        </li>

        <!-- Dropdown: Student -->
        <li>
          <details class="group" <?php if(in_array($current_page, ['add_student.php','submit_temporary_details.php','view_temporary_details.php','pending_admissions.php','view_students.php','blocked_students.php'])) echo 'open'; ?>>
            <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition">
              <i class="bx bxs-user-detail text-lg"></i>
              <span class="ml-3 nav-text">Student</span>
              <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
            </summary>
            <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
              <li><a href="./add_student.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='add_student.php')?'active-link text-white':''; ?>">Add Student</a></li>
              <li><a href="./submit_temporary_details.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='submit_temporary_details.php')?'active-link text-white':''; ?>">Pre Admission Form</a></li>
              <li><a href="./view_temporary_details.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='view_temporary_details.php')?'active-link text-white':''; ?>">Pre Admission</a></li>
              <li><a href="./pending_admissions.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='pending_admissions.php')?'active-link text-white':''; ?>">Pending Admissions</a></li>
              <li><a href="./view_students.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='view_students.php')?'active-link text-white':''; ?>">View Students</a></li>
              <li><a href="./blocked_students.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='blocked_students.php')?'active-link text-white':''; ?>">Blocked Students</a></li>
            </ul>
          </details>
        </li>

        <!-- Dropdown: Class -->
        <li>
          <details class="group" <?php if(in_array($current_page, ['add_grade.php','add_subject.php','add_timeTable.php','view_classes.php','assign_subjects.php','chapters.php','view_chapters.php'])) echo 'open'; ?>>
            <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition">
              <i class="bx bx-chalkboard text-lg"></i>
              <span class="ml-3 nav-text">Class</span>
              <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
            </summary>
            <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
              <li><a href="./add_grade.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='add_grade.php')?'active-link text-white':''; ?>">Add Grade</a></li>
              <li><a href="./add_subject.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='add_subject.php')?'active-link text-white':''; ?>">Add Subject</a></li>
              <li><a href="./add_timeTable.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='add_timeTable.php')?'active-link text-white':''; ?>">Add Timetable</a></li>
              <li><a href="./view_classes.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='view_classes.php')?'active-link text-white':''; ?>">View Classes</a></li>
              <li><a href="./assign_subjects.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='assign_subjects.php')?'active-link text-white':''; ?>">Assign Subject</a></li>
              <li><a href="./chapters.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='chapters.php')?'active-link text-white':''; ?>">Chapters</a></li>
              <li><a href="./view_chapters.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='view_chapters.php')?'active-link text-white':''; ?>">View Chapters</a></li>
            </ul>
          </details>
        </li>

        <!-- Dropdown: Messaging -->
        <li>
          <details class="group" <?php if(in_array($current_page, ['message.php','view_messages.php','recieved_messages.php'])) echo 'open'; ?>>
            <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition">
              <i class="bx bx-message-square-detail text-lg"></i>
              <span class="ml-3 nav-text">Messaging</span>
              <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
            </summary>
            <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
              <li><a href="./message.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='message.php')?'active-link text-white':''; ?>">Message</a></li>
              <li><a href="./view_messages.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='view_messages.php')?'active-link text-white':''; ?>">View Message</a></li>
              <li><a href="./recieved_messages.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='recieved_messages.php')?'active-link text-white':''; ?>">Received Message</a></li>
            </ul>
          </details>
        </li>

        <!-- Dropdown: Exams -->
        <li>
          <details class="group" <?php if(in_array($current_page, ['create_exam.php','exam_timetables.php','manage_exam.php','results.php','test_result.php'])) echo 'open'; ?>>
            <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition">
              <i class="bx bx-edit text-lg"></i>
              <span class="ml-3 nav-text">Exams</span>
              <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
            </summary>
            <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
              <li><a href="./create_exam.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='create_exam.php')?'active-link text-white':''; ?>">Create Exam</a></li>
              <li><a href="./exam_timetables.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='exam_timetables.php')?'active-link text-white':''; ?>">Exam Timetables</a></li>
              <li><a href="./manage_exam.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='manage_exam.php')?'active-link text-white':''; ?>">Manage Exam</a></li>
              <li><a href="./results.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='results.php')?'active-link text-white':''; ?>">Results</a></li>
              <li><a href="./test_result.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='test_result.php')?'active-link text-white':''; ?>">Test Results</a></li>
            </ul>
          </details>
        </li>

        <!-- Dropdown: Feedback -->
        <li>
          <details class="group" <?php if(in_array($current_page, ['feedback.php','subject_feedback.php'])) echo 'open'; ?>>
            <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition">
              <i class="bx bx-comment-detail text-lg"></i>
              <span class="ml-3 nav-text">Feedback</span>
              <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
            </summary>
            <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
              <li><a href="./feedback.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='feedback.php')?'active-link text-white':''; ?>">Class Feedback</a></li>
              <li><a href="./subject_feedback.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='subject_feedback.php')?'active-link text-white':''; ?>">Subject Feedback</a></li>
            </ul>
          </details>
        </li>

        <!-- Dropdown: Fee Management -->
        <li>
          <details class="group" <?php if(in_array($current_page, ['assign_payment.php','fee_management.php','otherpayments_registration.php','payment_history.php','students_record.php'])) echo 'open'; ?>>
            <summary class="flex items-center px-2 py-2 cursor-pointer hover:bg-yellow-600 rounded-md transition">
              <i class="bx bx-wallet text-lg"></i>
              <span class="ml-3 nav-text">Fee Management</span>
              <i class="bx bx-chevron-down ml-auto chevron nav-text"></i>
            </summary>
            <ul class="ml-9 mt-2 space-y-2 text-gray-200 nav-text">
              <li><a href="./assign_payment.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='assign_payment.php')?'active-link text-white':''; ?>">Assign Payment</a></li>
              <li><a href="./fee_management.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='fee_management.php')?'active-link text-white':''; ?>">Fee Management</a></li>
              <li><a href="./otherpayments_registration.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='otherpayments_registration.php')?'active-link text-white':''; ?>">Other Payments</a></li>
              <li><a href="./payment_history.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='payment_history.php')?'active-link text-white':''; ?>">Payment History</a></li>
              <li><a href="./students_record.php" class="dropdown-item block px-2 py-1 rounded-md <?php echo ($current_page=='students_record.php')?'active-link text-white':''; ?>">Student Records</a></li>
            </ul>
          </details>
        </li>

        <!-- Single Links -->
        <li>
          <a href="./calender.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page=='calender.php')?'active-link':''; ?>">
            <i class="bx bx-calendar-event text-lg"></i>
            <span class="ml-3 nav-text">Event</span>
          </a>
        </li>
        <li>
          <a href="./view_assignments.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page=='view_assignments.php')?'active-link':''; ?>">
            <i class="bx bx-task text-lg"></i>
            <span class="ml-3 nav-text">Assignments</span>
          </a>
        </li>
        <li>
          <a href="./create_notice.php" class="nav-link flex items-center px-2 py-2 rounded-md transition <?php echo ($current_page=='create_notice.php')?'active-link':''; ?>">
            <i class="bx bx-bell text-lg"></i>
            <span class="ml-3 nav-text">Notice</span>
          </a>
        </li>
      </ul>
    </div>

    <!-- Logout -->
    <div>
      <a href="login.php" class="nav-link flex items-center px-2 py-2 text-red-400 hover:bg-red-500 hover:text-white rounded-md transition">
        <i class="bx bx-log-out text-lg"></i>
        <span class="ml-3 nav-text">Log Out</span>
      </a>
    </div>
  </nav>
</div>

</body>
</html>
<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

// Get the logged-in username
$username = $_SESSION['user']['username'];

// Include database connection
include('db_connection.php');

// SQL query to fetch teacher details
$sql = "SELECT subject_id, grade_id, teaching_classes, profile_image FROM teacher WHERE username = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);

// Fetch teacher data
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the teacher exists
if ($teacher) {
    $subject_id = $teacher['subject_id'];
    $grade_id = $teacher['grade_id'];
    $teaching_classes = json_decode($teacher['teaching_classes'], true) ?: []; // Decode JSON or default to empty array
    $profile_image = $teacher['profile_image'];
} else {
    $teaching_classes = [];
    $subject_id = null;
}

// Fetch today’s schedule
$today = date('l'); // e.g., Monday
$sql_schedule = "SELECT t.day, t.period, t.subject, g.grade_name 
                FROM timetables t 
                JOIN grades g ON t.grade_id = g.id 
                WHERE t.day = ? AND t.grade_id IN (" . (empty($teaching_classes) ? '0' : implode(',', array_fill(0, count($teaching_classes), '?'))) . ")";
$stmt_schedule = $pdo->prepare($sql_schedule);
$stmt_schedule->execute(array_merge([$today], $teaching_classes));
$schedule = $stmt_schedule->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending assignments
$sql_assignments = "SELECT a.title, a.end_date, g.grade_name 
                   FROM assignments a 
                   JOIN grades g ON a.class_id = g.id 
                   WHERE a.username = ? AND a.end_date >= CURDATE() 
                   ORDER BY a.end_date ASC LIMIT 5";
$stmt_assignments = $pdo->prepare($sql_assignments);
$stmt_assignments->execute([$username]);
$assignments = $stmt_assignments->fetchAll(PDO::FETCH_ASSOC);

// Fetch attendance overview for today
$sql_attendance = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present 
                  FROM attendance a 
                  JOIN students s ON a.student_id = s.id 
                  WHERE a.teacher_user_id = ? AND a.attendance_date = CURDATE() 
                  AND s.grade_id IN (" . (empty($teaching_classes) ? '0' : implode(',', array_fill(0, count($teaching_classes), '?'))) . ")";
$stmt_attendance = $pdo->prepare($sql_attendance);
$stmt_attendance->execute(array_merge([$username], $teaching_classes));
$attendance = $stmt_attendance->fetch(PDO::FETCH_ASSOC) ?: ['total' => 0, 'present' => 0];

// Fetch daily attendance report
$today_date = date('Y-m-d');
$daily_attendance = ['total_attendance' => 0, 'total_present' => 0, 'total_absent' => 0];
try {
    $sql_daily_attendance = "SELECT 
        COUNT(a.id) AS total_attendance, 
        SUM(a.status = 'Present') AS total_present,
        SUM(a.status = 'Absent') AS total_absent
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    JOIN grades g ON s.grade_id = g.id
    WHERE a.attendance_date = ? AND a.teacher_user_id = ?";
    $stmt_daily_attendance = $pdo->prepare($sql_daily_attendance);
    $stmt_daily_attendance->execute([$today_date, $username]);
    $daily_attendance = $stmt_daily_attendance->fetch(PDO::FETCH_ASSOC) ?: ['total_attendance' => 0, 'total_present' => 0, 'total_absent' => 0];
} catch (PDOException $e) {
    error_log("Daily attendance query error: " . $e->getMessage());
    $daily_attendance = ['total_attendance' => 0, 'total_present' => 0, 'total_absent' => 0];
}

// Fetch chapter progress
$sql_chapters = "SELECT c.chapter_name, c.completion_status, c.finished_on_time, g.grade_name 
                FROM chapters c 
                JOIN grades g ON c.grade_id = g.id 
                WHERE c.subject_id = ? AND c.grade_id IN (" . (empty($teaching_classes) ? '0' : implode(',', array_fill(0, count($teaching_classes), '?'))) . ") 
                LIMIT 5";
$stmt_chapters = $pdo->prepare($sql_chapters);
$stmt_chapters->execute(array_merge([$subject_id], $teaching_classes));
$chapters = $stmt_chapters->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent unread messages
$messages = [];
try {
    if (empty($teaching_classes)) {
        $sql_messages = "SELECT content, timestamp 
                        FROM messages 
                        WHERE (receiver_username = ? OR (is_broadcast = 1 AND target_group = 'Teachers')) 
                        AND is_read = 0 
                        ORDER BY timestamp DESC LIMIT 3";
        $stmt_messages = $pdo->prepare($sql_messages);
        $stmt_messages->execute([$username]);
    } else {
        $sql_messages = "SELECT content, timestamp 
                        FROM messages 
                        WHERE (receiver_username = ? OR (is_broadcast = 1 AND (target_group = 'Teachers' OR grade_id IN (" . implode(',', array_fill(0, count($teaching_classes), '?')) . ")))) 
                        AND is_read = 0 
                        ORDER BY timestamp DESC LIMIT 3";
        $stmt_messages = $pdo->prepare($sql_messages);
        $stmt_messages->execute(array_merge([$username], $teaching_classes));
    }
    $messages = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Messages query error: " . $e->getMessage());
    $messages = [];
}

// Fetch upcoming exams/tests
$sql_exams = "SELECT e.title, e.start_date, g.grade_name 
             FROM exams e 
             JOIN grades g ON e.grade_id = g.id 
             WHERE e.start_date >= CURDATE() AND e.grade_id IN (" . (empty($teaching_classes) ? '0' : implode(',', array_fill(0, count($teaching_classes), '?'))) . ") 
             UNION 
             SELECT t.type AS title, t.test_date AS start_date, g.grade_name 
             FROM tests t 
             JOIN grades g ON t.grade_id = g.id 
             WHERE t.test_date >= CURDATE() AND t.teacher_username = ? 
             ORDER BY start_date ASC LIMIT 3";
$stmt_exams = $pdo->prepare($sql_exams);
$stmt_exams->execute(array_merge($teaching_classes, [$username]));
$exams = $stmt_exams->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending feedback
$feedbacks = [];
try {
    $sql_feedback = "SELECT f.feedback, g.grade_name 
                    FROM feedbacks f 
                    JOIN grades g ON f.grade_id = g.id 
                    WHERE f.teacher_username = ? AND f.status = 'Pending' 
                    LIMIT 3";
    $stmt_feedback = $pdo->prepare($sql_feedback);
    $stmt_feedback->execute([$username]);
    $feedbacks = $stmt_feedback->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Feedback query error: " . $e->getMessage());
    $feedbacks = [];
    $sql_feedback_fallback = "SELECT f.feedback, g.grade_name 
                            FROM feedbacks f 
                            JOIN grades g ON f.grade_id = g.id 
                            WHERE f.teacher_username = ? 
                            LIMIT 3";
    $stmt_feedback = $pdo->prepare($sql_feedback_fallback);
    $stmt_feedback->execute([$username]);
    $feedbacks = $stmt_feedback->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch recent notices and events
$sql_notices = "SELECT title, end_date 
               FROM notices 
               WHERE end_date >= CURDATE() 
               ORDER BY created_at DESC LIMIT 2";
$stmt_notices = $pdo->prepare($sql_notices);
$stmt_notices->execute();
$notices = $stmt_notices->fetchAll(PDO::FETCH_ASSOC);

$sql_events = "SELECT title, start_date 
              FROM events 
              WHERE start_date >= CURDATE() 
              ORDER BY start_date ASC LIMIT 2";
$stmt_events = $pdo->prepare($sql_events);
$stmt_events->execute();
$events = $stmt_events->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - Reliance International School</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /*========== GOOGLE FONTS ==========*/
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

        /*========== VARIABLES CSS ==========*/
        :root {
            --header-height: 3.5rem;
            --first-color: #6923D0;
            --first-color-light: #F4F0FA;
            --title-color: #19181B;
            --text-color: #58555E;
            --text-color-light: #A5A1AA;
            --body-color: #F9F6FD;
            --container-color: #FFFFFF;
            --font-medium: 500;
            --font-semi-bold: 600;
            --font-bold: 700;
            --z-fixed: 100;
        }

        /*========== BASE ==========*/
        *, ::before, ::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 1.5rem 1.5rem 0 6rem;
            font-family: 'Poppins', sans-serif;
            font-size: 0.938rem;
            background-color: var(--body-color);
            color: var(--text-color);
        }

        h1 {
            position: relative;
            display: inline-block;
            padding-bottom: 0.5rem;
            margin-bottom: 2rem;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50%;
            height: 3px;
            background: linear-gradient(to right, var(--first-color), transparent);
        }

        h3 {
            margin: 0;
            font-weight: var(--font-semi-bold);
        }

        a {
            text-decoration: none;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            color: var(--text-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeIn 0.5s ease-out;
        }

        .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .glass-card h3 {
            font-size: 1.2rem;
            font-weight: var(--font-bold);
            margin-bottom: 1rem;
            color: var(--title-color);
            display: flex;
            align-items: center;
        }

        .glass-card h3 i {
            margin-right: 0.5rem;
            font-size: 1.5rem;
            color: var(--first-color);
        }

        .glass-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .glass-card li {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            font-size: 0.9rem;
            color: var(--text-color);
        }

        .glass-card li:last-child {
            border-bottom: none;
        }

        .glass-card a {
            display: block;
            margin-top: 0.75rem;
            font-size: 0.85rem;
            color: #2563EB;
            font-weight: var(--font-medium);
            transition: color 0.2s ease;
        }

        .glass-card a:hover {
            color: #1E40AF;
        }

        .glass-card-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
        }

        .glass-card-table th, .glass-card-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .glass-card-table th {
            background-color: rgba(0, 0, 0, 0.05);
            font-weight: var(--font-semi-bold);
            color: var(--title-color);
        }

        .glass-card-table td {
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .glass-card-table tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .pulse {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { background: rgba(255, 255, 255, 0.1); }
            50% { background: rgba(255, 255, 255, 0.15); }
            100% { background: rgba(255, 255, 255, 0.1); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (min-width: 768px) {
            body {
                padding: 2rem 3rem 0 7rem;
            }
            .dashboard-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 1.5rem;
            }
        }

        @media (max-width: 320px) {
            body {
                padding: 1rem 1rem 0 4.5rem;
            }
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .glass-card, .glass-card:hover, .pulse {
                animation: none;
                transform: none;
            }
        }

        /* Fallback for browsers without backdrop-filter support */
        @supports not (backdrop-filter: blur(12px)) {
            .glass-card {
                background: #FFFFFF;
                border: 1px solid #E5E7EB;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }
        }
    </style>
</head>
<body>
    <!--========== NAV ==========-->
    <?php include('navbar.php'); ?>

    <!--========== CONTENTS ==========-->
    <main class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-8">Teacher Dashboard</h1>
        <div class="dashboard-grid grid gap-6">
            <!-- Today’s Schedule -->
            <div class="glass-card">
                <h3><i class='bx bx-calendar'></i>Today’s Schedule</h3>
                <ul>
                    <?php if (count($schedule) > 0): ?>
                        <?php foreach ($schedule as $item): ?>
                            <li>Period <?php echo htmlspecialchars($item['period']); ?>: <?php echo htmlspecialchars($item['subject']); ?> (<?php echo htmlspecialchars($item['grade_name']); ?>)</li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No classes scheduled for today.</li>
                    <?php endif; ?>
                </ul>
                <a href="timetable.php" aria-label="View full timetable">View Full Timetable</a>
            </div>

            <!-- Pending Assignments -->
            <div class="glass-card">
                <h3><i class='bx bx-task'></i>Pending Assignments</h3>
                <ul>
                    <?php if (count($assignments) > 0): ?>
                        <?php foreach ($assignments as $assignment): ?>
                            <li><?php echo htmlspecialchars($assignment['title']); ?> (Due: <?php echo htmlspecialchars($assignment['end_date']); ?>, <?php echo htmlspecialchars($assignment['grade_name']); ?>)</li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No pending assignments.</li>
                    <?php endif; ?>
                </ul>
                <a href="assignment.php" aria-label="Manage assignments">Manage Assignments</a>
            </div>

            <!-- Attendance Overview -->
            <div class="glass-card <?php echo $attendance['total'] > 0 ? 'pulse' : ''; ?>">
                <h3><i class='bx bx-check-circle'></i>Attendance Overview</h3>
                <ul>
                    <li>Total Students: <?php echo htmlspecialchars($attendance['total']); ?></li>
                    <li>Present Today: <?php echo htmlspecialchars($attendance['present']); ?></li>
                </ul>
                <a href="attendance.php" aria-label="Mark attendance">Mark Attendance</a>
            </div>

            <!-- Daily Attendance Report -->
            <div class="glass-card">
                <h3><i class='bx bx-bar-chart-alt-2'></i>Daily Attendance Report (<?php echo htmlspecialchars($today_date); ?>)</h3>
                <table class="glass-card-table">
                    <thead>
                        <tr>
                            <th>Total Attendance</th>
                            <th>Total Present</th>
                            <th>Total Absent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo htmlspecialchars($daily_attendance['total_attendance']); ?></td>
                            <td><?php echo htmlspecialchars($daily_attendance['total_present']); ?></td>
                            <td><?php echo htmlspecialchars($daily_attendance['total_absent']); ?></td>
                        </tr>
                    </tbody>
                </table>
                <a href="attendance.php" aria-label="View detailed attendance">View Detailed Attendance</a>
            </div>

            <!-- Chapter Progress -->
            <div class="glass-card">
                <h3><i class='bx bx-book'></i>Chapter Progress</h3>
                <ul>
                    <?php if (count($chapters) > 0): ?>
                        <?php foreach ($chapters as $chapter): ?>
                            <li><?php echo htmlspecialchars($chapter['chapter_name']); ?> (<?php echo htmlspecialchars($chapter['grade_name']); ?>): 
                                <?php echo $chapter['completion_status'] ? ($chapter['finished_on_time'] ? 'Completed On Time' : 'Completed Late') : 'In Progress'; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No chapters assigned.</li>
                    <?php endif; ?>
                </ul>
                <a href="chapters.php" aria-label="Manage chapters">Manage Chapters</a>
            </div>

            <!-- Recent Messages -->
            <div class="glass-card <?php echo count($messages) > 0 ? 'pulse' : ''; ?>">
                <h3><i class='bx bx-message-square-detail'></i>Recent Messages</h3>
                <ul>
                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $message): ?>
                            <li><?php echo htmlspecialchars(substr($message['content'], 0, 50)); ?>... (<?php echo htmlspecialchars($message['timestamp']); ?>)</li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No unread messages.</li>
                    <?php endif; ?>
                </ul>
                <a href="view_messages.php" aria-label="View all messages">View All Messages</a>
            </div>

            <!-- Upcoming Exams/Tests -->
            <div class="glass-card">
                <h3><i class='bx bx-edit'></i>Upcoming Exams/Tests</h3>
                <ul>
                    <?php if (count($exams) > 0): ?>
                        <?php foreach ($exams as $exam): ?>
                            <li><?php echo htmlspecialchars($exam['title']); ?> (<?php echo htmlspecialchars($exam['grade_name']); ?>, <?php echo htmlspecialchars($exam['start_date']); ?>)</li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No upcoming exams or tests.</li>
                    <?php endif; ?>
                </ul>
                <a href="tests.php" aria-label="Manage tests">Manage Tests</a>
            </div>

            <!-- Pending Feedback -->
            <div class="glass-card <?php echo count($feedbacks) > 0 ? 'pulse' : ''; ?>">
                <h3><i class='bx bx-comment-detail'></i>Pending Feedback</h3>
                <ul>
                    <?php if (count($feedbacks) > 0): ?>
                        <?php foreach ($feedbacks as $feedback): ?>
                            <li><?php echo htmlspecialchars(substr($feedback['feedback'], 0, 50)); ?>... (<?php echo htmlspecialchars($feedback['grade_name']); ?>)</li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No pending feedback.</li>
                    <?php endif; ?>
                </ul>
                <a href="feedback.php" aria-label="Submit feedback">Submit Feedback</a>
            </div>

            <!-- Notices and Events -->
            <div class="glass-card">
                <h3><i class='bx bx-bell'></i>Notices and Events</h3>
                <ul>
                    <?php if (count($notices) > 0 || count($events) > 0): ?>
                        <?php foreach ($notices as $notice): ?>
                            <li>Notice: <?php echo htmlspecialchars($notice['title']); ?> (Due: <?php echo htmlspecialchars($notice['end_date']); ?>)</li>
                        <?php endforeach; ?>
                        <?php foreach ($events as $event): ?>
                            <li>Event: <?php echo htmlspecialchars($event['title']); ?> (<?php echo htmlspecialchars($event['start_date']); ?>)</li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>No recent notices or events.</li>
                    <?php endif; ?>
                </ul>
                <a href="notice.php" aria-label="View all notices">View All Notices</a>
            </div>
        </div>
    </main>

    <!--========== MAIN JS ==========-->
    <script src="assets/js/main.js"></script>
    <?php include('notification.php'); ?>
</body>
</html>
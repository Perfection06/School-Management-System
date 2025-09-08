<?php
// Database connection
include("db_connection.php");

// Fetch today's attendance data grouped by grade
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT 
        g.grade_name AS grade, 
        COUNT(a.id) AS total_attendance, 
        SUM(a.status = 'Present') AS total_present,
        SUM(a.status = 'Absent') AS total_absent
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    JOIN grades g ON s.grade_id = g.id
    WHERE a.attendance_date = :today
    GROUP BY g.grade_name
");
$stmt->execute(['today' => $today]);
$gradeReports = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!-- generate_daily_report.php (Attendance Report) -->
<div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 animate-fadeIn overflow-x-auto">
    <h2 class="text-lg font-semibold mb-4 text-center text-gray-800">Daily Attendance Report for <?php echo htmlspecialchars($today); ?></h2>
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-3 border-b">Grade</th>
                <th class="p-3 border-b">Total Attendance</th>
                <th class="p-3 border-b">Total Present</th>
                <th class="p-3 border-b">Total Absent</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($gradeReports as $report): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-3 border-b"><?php echo htmlspecialchars($report['grade']); ?></td>
                    <td class="p-3 border-b"><?php echo htmlspecialchars($report['total_attendance']); ?></td>
                    <td class="p-3 border-b"><?php echo htmlspecialchars($report['total_present']); ?></td>
                    <td class="p-3 border-b"><?php echo htmlspecialchars($report['total_absent']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
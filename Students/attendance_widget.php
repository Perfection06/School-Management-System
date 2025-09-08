<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

// Get the student's username from the session
$username = htmlspecialchars($_SESSION['user']['username']);

include ("db_connection.php");
// Fetch the student ID from the database based on the username
$sqlStudentId = "
    SELECT id 
    FROM Students 
    WHERE username = :username
";
$stmt = $pdo->prepare($sqlStudentId);
$stmt->execute(['username' => $username]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "Student not found.";
    exit;
}

$student_id = $student['id'];

// Fetch total attendance counts (Present, Absent) for the current month
$sqlAttendance = "
    SELECT
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS total_present,
        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) AS total_absent
    FROM attendance
    WHERE student_id = :student_id
    AND YEAR(attendance_date) = YEAR(CURDATE()) 
    AND MONTH(attendance_date) = MONTH(CURDATE());
";
$stmtAttendance = $pdo->prepare($sqlAttendance);
$stmtAttendance->execute(['student_id' => $student_id]);
$attendanceSummary = $stmtAttendance->fetch(PDO::FETCH_ASSOC);

// Calculate attendance percentage
$total_days = ($attendanceSummary['total_present'] ?: 0) + ($attendanceSummary['total_absent'] ?: 0);
$attendance_percentage = $total_days > 0 ? round(($attendanceSummary['total_present'] / $total_days) * 100, 1) : 0;
?>

<div class="bg-white rounded-2xl shadow-lg p-6 transition-all hover:shadow-xl hover:-translate-y-1 duration-300 animate-fade-in backdrop-blur-sm bg-opacity-80">
    <div class="flex items-center gap-3 mb-4">
        <i class='bx bx-check-shield text-indigo-600 text-3xl'></i>
        <h3 class="text-lg font-bold text-gray-800">Attendance Summary</h3>
    </div>
    <p class="text-gray-500 mb-4 text-sm">Your attendance for <?= date('F Y') ?></p>
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="flex flex-col items-center justify-center bg-green-50 border border-green-200 rounded-lg p-4 hover:bg-green-100 transition-all">
            <span class="text-sm font-medium text-green-700">Present</span>
            <span class="text-2xl font-bold text-green-600 animate-pulse">
                <?= $attendanceSummary['total_present'] ?: 0 ?>
            </span>
        </div>
        <div class="flex flex-col items-center justify-center bg-red-50 border border-red-200 rounded-lg p-4 hover:bg-red-100 transition-all">
            <span class="text-sm font-medium text-red-700">Absent</span>
            <span class="text-2xl font-bold text-red-600 animate-pulse">
                <?= $attendanceSummary['total_absent'] ?: 0 ?>
            </span>
        </div>
    </div>
    <!-- Attendance Progress -->
    <div>
        <div class="flex mb-1 items-center justify-between">
            <span class="text-xs font-medium text-gray-600">Attendance Rate</span>
            <span class="text-xs font-bold text-indigo-600"><?= $attendance_percentage ?>%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-1.5">
            <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-500" style="width: <?= $attendance_percentage ?>%"></div>
        </div>
    </div>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.5s ease-in-out;
    }
</style>
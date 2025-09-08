<?php
session_start();


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

// Database connection
include ("db_connection.php");


// Get the teacher's user ID
$teacher_user_id = $_SESSION['user']['username'];
$attendance_date = date('Y-m-d');

// Process each student's attendance
foreach ($_POST['attendance'] as $student_id => $status) {
    // Check if attendance already exists for this student and date
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id = :student_id AND attendance_date = :attendance_date");
    $stmt->execute([
        'student_id' => $student_id,
        'attendance_date' => $attendance_date
    ]);
    $attendance_exists = $stmt->fetchColumn() > 0;

    if ($attendance_exists) {
        // Update existing attendance record
        $stmt = $pdo->prepare("UPDATE attendance SET status = :status, teacher_user_id = :teacher_user_id WHERE student_id = :student_id AND attendance_date = :attendance_date");
    } else {
        // Insert new attendance record
        $stmt = $pdo->prepare("INSERT INTO attendance (student_id, teacher_user_id, attendance_date, status) VALUES (:student_id, :teacher_user_id, :attendance_date, :status)");
    }
    
    $stmt->execute([
        'student_id' => $student_id,
        'teacher_user_id' => $teacher_user_id,
        'attendance_date' => $attendance_date,
        'status' => $status
    ]);
}

echo "<script>
            alert('Attendance marked successfully!');
            window.location.href = 'Teacher_Dashboard.php';
        </script>";
exit();

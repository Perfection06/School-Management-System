<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

include ("db_connection.php");

// Check if the student ID and exam ID are set
$student_id = isset($_POST['student_id']) ? $_POST['student_id'] : null;
$exam_id = isset($_POST['exam_id']) ? $_POST['exam_id'] : null;

$student_name = '';
$exam_title = '';

// Fetch student's name if the student ID is set
if ($student_id) {
    $student_stmt = $pdo->prepare("SELECT name FROM Students WHERE id = :student_id");
    $student_stmt->execute(['student_id' => $student_id]);
    $student_name = $student_stmt->fetchColumn();
}

// Fetch exam title if the exam ID is set
if ($exam_id) {
    $exam_stmt = $pdo->prepare("SELECT exam_title FROM Exams WHERE id = :exam_id");
    $exam_stmt->execute(['exam_id' => $exam_id]);
    $exam_title = $exam_stmt->fetchColumn();
}

if ($student_id && $exam_id) {
    // Fetch marks for the selected student and exam
    $marks_stmt = $pdo->prepare("
        SELECT subject_id, marks 
        FROM Marks 
        WHERE student_id = :student_id AND exam_id = :exam_id
    ");
    $marks_stmt->execute(['student_id' => $student_id, 'exam_id' => $exam_id]);
    $marks = $marks_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $marks = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Marks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .back-button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Marks for <?= htmlspecialchars($student_name) ?> in Exam: <?= htmlspecialchars($exam_title) ?></h2>

    <?php if (count($marks) > 0): ?>
        <table>
            <tr>
                <th>Subject ID</th>
                <th>Marks</th>
            </tr>
            <?php foreach ($marks as $mark): ?>
                <tr>
                    <td><?= htmlspecialchars($mark['subject_id']) ?></td>
                    <td><?= htmlspecialchars($mark['marks']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No marks found for this student in the selected exam.</p>
    <?php endif; ?>

    <!-- Back Button -->
    <a href="view_results.php" class="back-button">Back</a>
</div>

</body>
</html>

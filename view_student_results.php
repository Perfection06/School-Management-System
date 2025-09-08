<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}


include("db_connection.php");

// Get student ID and exam ID from the POST request
$student_id = $_POST['student_id'];
$exam_id = $_POST['exam_id'];

// Fetch student name
$student_stmt = $pdo->prepare("SELECT name FROM Students WHERE id = :student_id");
$student_stmt->execute(['student_id' => $student_id]);
$student_name = $student_stmt->fetchColumn();

// Fetch exam title
$exam_stmt = $pdo->prepare("SELECT exam_title FROM Exams WHERE id = :exam_id");
$exam_stmt->execute(['exam_id' => $exam_id]);
$exam_title = $exam_stmt->fetchColumn();

// Fetch subject marks for the student in the selected exam
$marks_stmt = $pdo->prepare("
    SELECT Subjects.subject_name, Marks.marks 
    FROM Marks 
    JOIN Subjects ON Marks.subject_id = Subjects.id 
    WHERE Marks.student_id = :student_id AND Marks.exam_id = :exam_id
");
$marks_stmt->execute(['student_id' => $student_id, 'exam_id' => $exam_id]);
$marks = $marks_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Details</title>
    <style>
        /* Base styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        /* Container */
        .container {
            width: 80%;
            max-width: 800px;
            background-color: #fff;
            padding: 2em;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        /* Header */
        h2 {
            color: #2c3e50;
            font-size: 1.5em;
            margin-bottom: 1.5em;
            border-bottom: 2px solid #3498db;
            display: inline-block;
            padding-bottom: 0.3em;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1em;
            font-size: 1em;
        }

        th, td {
            padding: 0.75em;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: #fff;
            font-weight: bold;
        }

        td {
            background-color: #f9f9f9;
        }

        /* Row hover effect */
        tr:nth-child(even) td {
            background-color: #eef4f8;
        }

        /* Button styling */
        .btn {
            display: inline-block;
            margin-top: 1.5em;
            padding: 0.75em 1.5em;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        /* Hide print button on print */
        @media print {
            .btn {
                display: none;
            }
            body {
                background-color: #fff;
            }
        }
    </style>
</head>
<body>

<div class="container" id="resultContainer">
    <h2>Results for <?= htmlspecialchars($student_name) ?> (Exam: <?= htmlspecialchars($exam_title) ?>)</h2>

    <table>
        <tr>
            <th>Subject</th>
            <th>Marks</th>
        </tr>
        <?php foreach ($marks as $mark): ?>
            <tr>
                <td><?= htmlspecialchars($mark['subject_name']) ?></td>
                <td><?= htmlspecialchars($mark['marks']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <!-- Print and Back buttons -->
    <button class="btn" onclick="window.print()">Print Results</button>
    <a href="view_result.php" class="btn">Back to Results</a>
</div>

<script>
    function printResults() {
        window.print();
    }
</script>

</body>
</html>

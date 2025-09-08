<?php
include("db_connection.php");
session_start();

// Verify the student session
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['user']['username']);

// Fetch student's grade_id using username
$query = "SELECT grade_id FROM Students WHERE username = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$username]);
$studentGrade = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$studentGrade) {
    echo "No grade found for this student.";
    exit;
}

$gradeId = $studentGrade['grade_id'];

// Fetch the most recently created exam for the grade
$sql = "
    SELECT e.title, e.term, es.exam_date, es.exam_time, s.subject_name
    FROM exams e
    JOIN exam_subjects es ON e.id = es.exam_id
    JOIN subjects s ON es.subject_id = s.id
    WHERE e.grade_id = ?
      AND e.id = (
          SELECT id
          FROM exams
          WHERE grade_id = ?
          ORDER BY created_at DESC
          LIMIT 1
      )
    ORDER BY es.exam_date, es.exam_time
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$gradeId, $gradeId]);
$examTimetable = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$examTimetable) {
    echo "No exam timetable available for your grade.";
    exit;
}

// Display the timetable as needed
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Exam Timetable</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        h2 {
            margin-top: 30px;
            text-align: center;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        td {
            background-color: #f8f8f8;
        }
        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .buttons button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .back-button {
            background-color: #6c757d;
            color: #fff;
        }
        .back-button:hover {
            background-color: #5a6268;
        }
        .print-button {
            background-color: #007bff;
            color: #fff;
        }
        .print-button:hover {
            background-color: #0056b3;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .print-section, .print-section * {
                visibility: visible;
            }
            .print-section {
                position: absolute;
                top: 0;
                left: 0;
            }
        }
    </style>
    <script>
        function printTimetable() {
            window.print();
        }
    </script>
</head>
<body>

<h1>Student Exam Timetable</h1>

<!-- Back Button -->
<div class="buttons">
    <button class="back-button" onclick="window.location.href='Student_Dashboard.php'">Back to Dashboard</button>
    <button class="print-button" onclick="printTimetable()">Print Timetable</button>
</div>

<!-- Timetable Section -->
<div class="print-section">
    <?php if (count($examTimetable) > 0): ?>
        <?php
        $currentTitle = '';
        $currentTerm = '';
        $currentDate = ''; // Track dates for merging rows
        ?>
        <?php foreach ($examTimetable as $row): ?>
            <?php if ($row['title'] !== $currentTitle || $row['term'] !== $currentTerm): ?>
                <?php if ($currentTitle !== '' && $currentTerm !== ''): ?>
                    </tbody></table>
                <?php endif; ?>
                <h2><?= htmlspecialchars($row['title']) ?> (Term <?= htmlspecialchars($row['term']) ?>)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php
                $currentTitle = $row['title'];
                $currentTerm = $row['term'];
                $currentDate = ''; // Reset the date tracker for each new table
            endif;

            // Format time in AM/PM format
            $formattedTime = date("g:i A", strtotime($row['exam_time']));

            // Merge rows for the same date
            $mergeDate = ($row['exam_date'] === $currentDate) ? '' : $row['exam_date'];
            $currentDate = $row['exam_date']; // Update the current date
            ?>
            <tr>
                <td><?= htmlspecialchars($row['subject_name']); ?></td>
                <?php if ($mergeDate): ?>
                    <td rowspan="<?= array_count_values(array_column($examTimetable, 'exam_date'))[$row['exam_date']] ?>">
                        <?= htmlspecialchars($mergeDate); ?>
                    </td>
                <?php endif; ?>
                <td><?= htmlspecialchars($formattedTime); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table>
    <?php else: ?>
        <p style="text-align:center;">No exams found for your grade.</p>
    <?php endif; ?>
</div>

</body>
</html>

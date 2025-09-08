<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

// Database connection
include("db_connection.php");

// Get the logged-in teacher's username
$username = $_SESSION['user']['username'];

// Fetch teacher details
$teacherQuery = "
    SELECT t.subject_id, t.teaching_classes, t.grade_id
    FROM teacher t
    WHERE t.username = ?
";
$teacherStmt = $pdo->prepare($teacherQuery);
$teacherStmt->execute([$username]);
$teacherData = $teacherStmt->fetch(PDO::FETCH_ASSOC);

if (!$teacherData) {
    echo "No data found for the logged-in teacher.";
    exit;
}

// Decode teaching_classes (JSON format)
$teachingClasses = json_decode($teacherData['teaching_classes'], true);
$subjectId = $teacherData['subject_id'];
$assignedGradeId = $teacherData['grade_id'];

// Fetch exams for assigned subjects
$examTimetable = [];
if (!empty($teachingClasses)) {
    $placeholders = implode(',', array_fill(0, count($teachingClasses), '?'));

    $examQuery = "
        SELECT e.title, e.term, es.exam_date, es.exam_time, sub.subject_name, e.grade_id
        FROM exams e
        INNER JOIN exam_subjects es ON e.id = es.exam_id
        INNER JOIN subjects sub ON es.subject_id = sub.id
        WHERE es.subject_id = ? 
        AND e.grade_id IN ($placeholders)
        AND CURDATE() <= e.publish_date
        ORDER BY e.term, es.exam_date, es.exam_time
    ";
    $examStmt = $pdo->prepare($examQuery);
    $examStmt->execute(array_merge([$subjectId], $teachingClasses));
    $examTimetable = $examStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch exams for assigned grades
$classExamTimetable = [];
if ($assignedGradeId) {
    $classExamQuery = "
        SELECT e.title, e.term, es.exam_date, es.exam_time, sub.subject_name, e.grade_id
        FROM exams e
        INNER JOIN exam_subjects es ON e.id = es.exam_id
        INNER JOIN subjects sub ON es.subject_id = sub.id
        WHERE e.grade_id = ?
        AND CURDATE() <= e.publish_date
        ORDER BY e.term, es.exam_date, es.exam_time
    ";
    $classExamStmt = $pdo->prepare($classExamQuery);
    $classExamStmt->execute([$assignedGradeId]);
    $classExamTimetable = $classExamStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Helper function to calculate rowspan values for merging dates
function calculateRowspans($timetable) {
    $rowspans = [];
    foreach ($timetable as $row) {
        $date = $row['exam_date'];
        if (!isset($rowspans[$date])) {
            $rowspans[$date] = 0;
        }
        $rowspans[$date]++;
    }
    return $rowspans;
}

// Calculate rowspans for subject and class timetables
$subjectRowspans = calculateRowspans($examTimetable);
$classRowspans = calculateRowspans($classExamTimetable);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Timetable</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #0056b3;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .print-button {
            text-align: right;
            margin-bottom: 20px;
        }
        .print-button button {
            background-color: #0056b3;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        .print-button button:hover {
            background-color: #004494;
        }
    </style>
    <script>
        function printTimetable() {
            window.print();
        }
    </script>
</head>
<body>
<?php include('navbar.php'); ?>
    <h1>Exam Timetable</h1>

    <!-- Display Subject-Based Exams -->
    <?php if (count($examTimetable) > 0): ?>
        <h2>Subject Exams</h2>
        <?php
        $currentTitle = '';
        $currentTerm = '';
        ?>
        <?php foreach ($examTimetable as $index => $row): ?>
            <?php if ($row['title'] !== $currentTitle || $row['term'] !== $currentTerm): ?>
                <?php if ($currentTitle !== '' && $currentTerm !== ''): ?>
                    </tbody></table>
                    <div class="print-button">
                        <button onclick="printTimetable()">Print Timetable</button>
                    </div>
                <?php endif; ?>
                <h3><?= htmlspecialchars($row['title']); ?> (Term <?= htmlspecialchars($row['term']); ?>)</h3>
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
            endif;

            $formattedTime = date("g:i A", strtotime($row['exam_time']));
            $mergeDate = ($index === 0 || $row['exam_date'] !== $examTimetable[$index - 1]['exam_date']) ? $row['exam_date'] : '';
            ?>
            <tr>
                <td><?= htmlspecialchars($row['subject_name']); ?></td>
                <?php if ($mergeDate): ?>
                    <td rowspan="<?= $subjectRowspans[$mergeDate]; ?>">
                        <?= htmlspecialchars($mergeDate); ?>
                    </td>
                <?php endif; ?>
                <td><?= htmlspecialchars($formattedTime); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table>
        <div class="print-button">
            <button onclick="printTimetable()">Print Timetable</button>
        </div>
    <?php else: ?>
        <p>No subject exams found.</p>
    <?php endif; ?>

    <!-- Display Class-Based Exams -->
    <?php if (count($classExamTimetable) > 0): ?>
        <h2>Class Exams</h2>
        <?php
        $currentTitle = '';
        $currentTerm = '';
        ?>
        <?php foreach ($classExamTimetable as $index => $row): ?>
            <?php if ($row['title'] !== $currentTitle || $row['term'] !== $currentTerm): ?>
                <?php if ($currentTitle !== '' && $currentTerm !== ''): ?>
                    </tbody></table>
                    <div class="print-button">
                        <button onclick="printTimetable()">Print Timetable</button>
                    </div>
                <?php endif; ?>
                <h3><?= htmlspecialchars($row['title']); ?> (Term <?= htmlspecialchars($row['term']); ?>)</h3>
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
            endif;

            $formattedTime = date("g:i A", strtotime($row['exam_time']));
            $mergeDate = ($index === 0 || $row['exam_date'] !== $classExamTimetable[$index - 1]['exam_date']) ? $row['exam_date'] : '';
            ?>
            <tr>
                <td><?= htmlspecialchars($row['subject_name']); ?></td>
                <?php if ($mergeDate): ?>
                    <td rowspan="<?= $classRowspans[$mergeDate]; ?>">
                        <?= htmlspecialchars($mergeDate); ?>
                    </td>
                <?php endif; ?>
                <td><?= htmlspecialchars($formattedTime); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table>
        <div class="print-button">
            <button onclick="printTimetable()">Print Timetable</button>
        </div>
    <?php else: ?>
        <p>No class exams found.</p>
    <?php endif; ?>
</body>
</html>

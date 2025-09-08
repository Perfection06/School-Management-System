<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'NoClass_Teacher') {
    header("Location: login.php");
    exit;
}

// Database connection
include("db_connection.php");

// Get the logged-in teacher's username
$username = $_SESSION['user']['username'];

// Fetch teacher details from noclass_teacher table
$teacherQuery = "
    SELECT nt.subject_id, nt.teaching_classes
    FROM noclass_teacher nt
    WHERE nt.username = ?
";
$teacherStmt = $pdo->prepare($teacherQuery);
$teacherStmt->execute([$username]);
$teacherData = $teacherStmt->fetch(PDO::FETCH_ASSOC);

if (!$teacherData) {
    echo "No data found for the logged-in teacher.";
    exit;
}

// Decode teaching_classes (JSON format)
$teachingClasses = json_decode($teacherData['teaching_classes'], true); // Grade 1, Grade 2

// Fetch the distinct terms that have exams for the grades the teacher is assigned to
$termQuery = "
    SELECT DISTINCT e.term
    FROM exams e
    WHERE e.grade_id IN (" . implode(',', $teachingClasses) . ")
    AND e.publish_date <= CURDATE()  -- Only show exams where publish_date is today or in the future
    ORDER BY e.term
";
$termStmt = $pdo->prepare($termQuery);
$termStmt->execute();
$terms = $termStmt->fetchAll(PDO::FETCH_ASSOC);

// Set the selected term (default to first term if not set)
$selectedTerm = isset($_GET['term']) ? $_GET['term'] : (isset($terms[0]) ? $terms[0]['term'] : '');

// Fetch exams for the selected term and grades the teacher is assigned to
$examTimetable = [];
if (!empty($teachingClasses)) {
    // Prepare placeholders for the teaching classes (grades)
    $placeholders = implode(',', array_fill(0, count($teachingClasses), '?'));

    // Query to get exams for the grades and selected term
    $examQuery = "
        SELECT e.title, e.term, es.exam_date, es.exam_time, sub.subject_name, e.grade_id
        FROM exams e
        INNER JOIN exam_subjects es ON e.id = es.exam_id
        INNER JOIN subjects sub ON es.subject_id = sub.id
        WHERE e.grade_id IN ($placeholders)
        AND e.term = ?
        AND e.publish_date <= CURDATE()  -- Only show exams where publish_date is today or in the future
        ORDER BY e.grade_id, e.term, es.exam_date, es.exam_time
    ";
    $examStmt = $pdo->prepare($examQuery);
    $examStmt->execute(array_merge($teachingClasses, [$selectedTerm]));
    $examTimetable = $examStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Group the exams by grade
$groupedExams = [];
foreach ($examTimetable as $exam) {
    $groupedExams[$exam['grade_id']][] = $exam;
}
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
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        header {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 1.5rem;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            color: #4CAF50;
            margin-top: 20px;
        }

        h3 {
            color: #555;
            margin-top: 20px;
        }

        label {
            font-weight: bold;
        }

        select {
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table thead th {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: left;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tbody tr td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        table tbody tr td:first-child {
            font-weight: bold;
        }

        p {
            text-align: center;
            font-size: 1rem;
            color: #777;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            color: #777;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>
    <header>Exam Timetable</header>
    <div class="container">
        <!-- Display the Term Dropdown -->
        <form method="GET" action="">
            <label for="term">Select Term:</label>
            <select name="term" id="term" onchange="this.form.submit()">
                <?php foreach ($terms as $term): ?>
                    <option value="<?= htmlspecialchars($term['term']); ?>" <?= ($term['term'] == $selectedTerm) ? 'selected' : ''; ?>>
                        Term <?= htmlspecialchars($term['term']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Display the Exam Timetable -->
        <div>
            <?php if (!empty($groupedExams)): ?>
                <?php foreach ($groupedExams as $gradeId => $gradeExams): ?>
                    <h2>Grade <?= htmlspecialchars($gradeId) ?> Timetable (Term <?= htmlspecialchars($selectedTerm) ?>)</h2>
                    <?php if (!empty($gradeExams)): ?>
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
                                $rowspans = array_count_values(array_column($gradeExams, 'exam_date'));
                                $currentDate = '';

                                foreach ($gradeExams as $row):
                                    // Format time in AM/PM format
                                    $formattedTime = date("g:i A", strtotime($row['exam_time']));

                                    // Merge rows for the same date
                                    $mergeDate = ($row['exam_date'] === $currentDate) ? '' : $row['exam_date'];
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['subject_name']); ?></td>
                                        <?php if ($mergeDate): ?>
                                            <td rowspan="<?= $rowspans[$row['exam_date']]; ?>">
                                                <?= htmlspecialchars($row['exam_date']); ?>
                                            </td>
                                        <?php endif; ?>
                                        <td><?= htmlspecialchars($formattedTime); ?></td>
                                    </tr>
                                    <?php $currentDate = $row['exam_date']; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No exams found for Grade <?= htmlspecialchars($gradeId) ?> in Term <?= htmlspecialchars($selectedTerm) ?>.</p>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No exams found for your teaching classes in Term <?= htmlspecialchars($selectedTerm) ?>.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

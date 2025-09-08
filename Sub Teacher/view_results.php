<?php
include("db_connection.php");
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'NoClass_Teacher') {
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['user']['username']);

// Fetch no_class_teacher's assigned subjects
$query = "SELECT teaching_classes FROM noclass_teacher WHERE username = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$username]);
$teacherInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacherInfo) {
    echo "No assigned subjects found.";
    exit;
}

// Decode the teaching classes (assuming it is stored as a JSON array)
$teachingClasses = json_decode($teacherInfo['teaching_classes'], true);

// Fetch exams for the subjects the no_class_teacher teaches
$examQuery = "
    SELECT DISTINCT e.id, e.title, e.term, e.start_date, e.publish_date
    FROM exams e
    JOIN exam_subjects es ON es.exam_id = e.id
    WHERE es.subject_id IN (" . implode(",", array_map('intval', $teachingClasses)) . ")
    ORDER BY e.term, e.start_date";

$examStmt = $pdo->prepare($examQuery);
$examStmt->execute();
$exams = $examStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle POST request for viewing exam results
$examData = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['view_exam'])) {
    $exam_id = $_POST['exam_id'];

    // Fetch student data and their marks for the selected exam
    $marksQuery = "
        SELECT s.username, s.name, e.id AS exam_id, e.title AS exam_title, e.term, e.publish_date, sub.subject_name, sm.marks
        FROM students s
        CROSS JOIN exams e ON e.grade_id = s.grade_id
        LEFT JOIN exam_subjects es ON es.exam_id = e.id
        LEFT JOIN subjects sub ON es.subject_id = sub.id
        LEFT JOIN student_marks sm ON sm.exam_id = e.id AND sm.subject_id = es.subject_id AND sm.student_username = s.username
        WHERE e.id = ?
        ORDER BY e.term, e.id, s.username, sub.subject_name";
    
    $stmt = $pdo->prepare($marksQuery);
    $stmt->execute([$exam_id]);
    $marksData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group data by exam
    if ($marksData) {
        foreach ($marksData as $row) {
            $examId = $row['exam_id'];
            $username = $row['username'];
            $subject = $row['subject_name'];

            if (!isset($examData[$examId])) {
                $examData[$examId] = [
                    'title' => $row['exam_title'],
                    'term' => $row['term'],
                    'publish_date' => $row['publish_date'],
                    'students' => [],
                ];
            }

            if (!isset($examData[$examId]['students'][$username])) {
                $examData[$examId]['students'][$username] = [
                    'name' => $row['name'],
                    'subjects' => [],
                    'total' => 0,
                ];
            }

            $marks = $row['marks'];
            $examData[$examId]['students'][$username]['subjects'][$subject] = $marks !== null ? $marks : 'N/A';
            $examData[$examId]['students'][$username]['total'] += $marks !== null ? $marks : 0;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Ranking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            background-color: #4CAF50;
            color: white;
            padding: 20px 0;
            margin: 0;
        }
        h2 {
            text-align: center;
            color: #4CAF50;
            margin-top: 20px;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            text-align: center;
            padding: 12px;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        button:hover {
            background-color: #45a049;
        }
        .center {
            text-align: center;
            margin: 20px;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <h1>Student Results - No Class Teacher</h1>

    <form method="POST" action="">
        <h2>Select an Exam to View Results</h2>
        <select name="exam_id" required>
            <option value="">-- Select Exam --</option>
            <?php foreach ($exams as $exam): ?>
                <option value="<?php echo $exam['id']; ?>">
                    <?php echo "{$exam['title']} ({$exam['term']} term) - " . date('d M Y', strtotime($exam['start_date'])); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="view_exam">View Results</button>
    </form>

    <?php if (!empty($examData)): ?>
        <?php foreach ($examData as $examId => $exam): ?>
            <h2><?php echo "{$exam['title']} ({$exam['term']} term)"; ?></h2>
            <table>
                <tr>
                    <th>Student Name</th>
                    <?php
                    $subjects = array_keys(current($exam['students'])['subjects']);
                    foreach ($subjects as $subject): ?>
                        <th><?php echo htmlspecialchars($subject); ?></th>
                    <?php endforeach; ?>
                    <th>Total Marks</th>
                </tr>
                <?php foreach ($exam['students'] as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <?php foreach ($subjects as $subject): ?>
                            <td><?php echo $student['subjects'][$subject] ?? 'N/A'; ?></td>
                        <?php endforeach; ?>
                        <td><?php echo $student['total']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>

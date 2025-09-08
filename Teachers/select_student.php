<?php
session_start();

// Check if the user is a logged-in teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'sms';
$dbUsername = 'root';
$dbPassword = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get the teacher's assigned class ID
$assigned_class_id = $_SESSION['assigned_class_id'];

// Fetch exams for this teacher's class, excluding those that are more than 10 days past their end date
$currentDate = date('Y-m-d');
$exam_stmt = $pdo->prepare("
    SELECT * FROM Exams 
    WHERE class_id = :class_id AND end_date >= DATE_SUB(CURDATE(), INTERVAL 10 DAY)
");
$exam_stmt->execute(['class_id' => $assigned_class_id]);
$exams = $exam_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if an exam is selected
$selected_exam_id = isset($_GET['exam_id']) ? $_GET['exam_id'] : null;

// Fetch students in the assigned class only if an exam is selected
if ($selected_exam_id) {
    $student_stmt = $pdo->prepare("SELECT * FROM Students WHERE grade COLLATE utf8mb4_unicode_ci = (SELECT grade_name FROM grades WHERE id = :class_id)");
    $student_stmt->execute(['class_id' => $assigned_class_id]);
    $students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $students = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Student</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
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
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        select {
            padding: 10px;
            font-size: 1em;
            border-radius: 4px;
            border: 1px solid #ddd;
            width: 100%;
            max-width: 300px;
            margin: 0 auto 20px;
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .back-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container">
    <h2>Select an Exam to Enter Marks</h2>

    <!-- Display exams -->
    <form method="GET" action="">
        <label for="exam">Choose an exam:</label>
        <select id="exam" name="exam_id" onchange="this.form.submit()">
            <option value="">Select an exam</option>
            <?php if (count($exams) > 0): ?>
                <?php foreach ($exams as $exam): ?>
                    <option value="<?= htmlspecialchars($exam['id']) ?>" <?= $selected_exam_id == $exam['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($exam['exam_title']) ?>
                    </option>
                <?php endforeach; ?>
            <?php else: ?>
                <option value="">No available exams</option>
            <?php endif; ?>
        </select>
    </form>

    <!-- If an exam is selected, show the students -->
    <?php if ($selected_exam_id): ?>
        <h2>Select a Student to Enter Marks</h2>
        <?php if (count($students) > 0): ?>
            <table>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['id']) ?></td>
                        <td><?= htmlspecialchars($student['name']) ?></td>
                        <td><a href="enter_marks.php?student_id=<?= $student['id'] ?>&exam_id=<?= $selected_exam_id ?>">Enter Marks</a></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No students available for this exam.</p>
        <?php endif; ?>
    <?php endif; ?>

</div>

</body>
</html>

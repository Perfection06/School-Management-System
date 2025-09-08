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

// Get the teacher's username and assigned class ID
$teacher_username = $_SESSION['user_id'];
$assigned_class_id = $_SESSION['assigned_class_id'];

// Fetch exams for this teacher's class
$exam_stmt = $pdo->prepare("SELECT * FROM Exams WHERE teacher_username = :teacher_username AND class_id = :class_id");
$exam_stmt->execute([
    'teacher_username' => $teacher_username,
    'class_id' => $assigned_class_id
]);
$exams = $exam_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the current date and time
$current_date = new DateTime();

// Set timezone if needed
// $current_date->setTimezone(new DateTimeZone('Your/Timezone')); // Adjust as necessary

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Exam</title>
    <style>
        /* Base styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f8;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        /* Container styling */
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
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

        /* Action links styling */
        a.create-timetable-link {
            display: inline-block;
            padding: 0.5em 1em;
            background-color: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        a.create-timetable-link:hover {
            background-color: #2980b9;
        }

        /* Disabled message styling */
        .disabled-message {
            color: #e74c3c;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container">
    <?php include('navbar.php'); ?>
    
    <h2>Select an Exam to Create Timetable</h2>

    <table>
        <tr>
            <th>Exam Title</th>
            <th>Action</th>
        </tr>
        <?php 
        foreach ($exams as $exam): 
            $start_date = new DateTime($exam['start_date']); // Use start_date for the exam date
            $two_days_before = clone $start_date;
            $two_days_before->modify('-2 days'); // Get the date two days before the start date
        ?>
            <tr>
                <td><?= htmlspecialchars($exam['exam_title']) ?></td>
                <td>
                    <?php if ($current_date >= $two_days_before): ?>
                        <span class="disabled-message">Cannot modify timetable</span>
                    <?php else: ?>
                        <a href="exam_timetable.php?exam_id=<?= $exam['id'] ?>" class="create-timetable-link">Create Timetable</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>

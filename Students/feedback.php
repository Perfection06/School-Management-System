<?php
session_start();

include("db_connection.php");

// Check if the user is logged in and is a Student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

// Fetch student username from session
$username = $_SESSION['user']['username'];

// Fetch the student's grade ID from the database
$stmt = $pdo->prepare("
    SELECT grade_id 
    FROM Students 
    WHERE username = :username
");
$stmt->execute(['username' => $username]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "Error: Student details not found.";
    exit;
}

// Fetch feedback for the student's grade
$stmt = $pdo->prepare("
    SELECT f.feedback, f.feedback_date, t.full_name AS teacher_name
    FROM feedbacks f
    JOIN teacher t ON f.teacher_username = t.username
    WHERE f.grade_id = :grade_id
");
$stmt->execute(['grade_id' => $student['grade_id']]);
$feedback = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grade Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #4CAF50;
        }
        .feedback-card {
            margin-top: 20px;
        }
        .feedback-card h3 {
            margin: 0;
            color: #333;
        }
        .feedback-card .date {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
        }
        .feedback-card .content {
            margin-top: 15px;
            font-size: 16px;
            color: #333;
        }
        .no-feedback {
            text-align: center;
            margin-top: 20px;
            color: #888;
            font-size: 16px;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="container">
        <h2>My Grade Feedback</h2>

        <?php if ($feedback): ?>
            <div class="feedback-card">
                <h3>Teacher: <?= htmlspecialchars($feedback['teacher_name']) ?></h3>
                <p class="date">Feedback Date: <?= htmlspecialchars($feedback['feedback_date']) ?></p>
                <div class="content"><?= nl2br(htmlspecialchars($feedback['feedback'])) ?></div>
            </div>
        <?php else: ?>
            <p class="no-feedback">No feedback available for your grade at the moment.</p>
        <?php endif; ?>
    </div>
</body>
</html>

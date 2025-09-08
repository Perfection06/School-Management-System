<?php
session_start();
include("db_connection.php");

// Check if the user is logged in and is a student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

// Fetch the logged-in student's details
$student_username = $_SESSION['user']['username'];
$stmt = $pdo->prepare("SELECT id, grade_id FROM Students WHERE username = :username");
$stmt->execute(['username' => $student_username]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo json_encode(['error' => 'Student details not found']);
    exit;
}

// Get the student's grade ID
$student_grade_id = $student['grade_id'];

// Fetch grades and subjects for the student's grade
$stmt = $pdo->prepare("SELECT id, grade_name FROM grades WHERE id = :grade_id");
$stmt->execute(['grade_id' => $student_grade_id]);
$grade = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$grade) {
    echo json_encode(['error' => 'Grade details not found']);
    exit;
}

// Fetch feedback for the student's grade
$stmt = $pdo->prepare("
    SELECT sf.id, g.grade_name, s.subject_name, sf.teacher_username, sf.feedback, sf.feedback_date, u.role, t.full_name, nt.full_name AS noclass_teacher_name
    FROM subject_feedbacks sf
    JOIN grades g ON sf.grade_id = g.id
    JOIN subjects s ON sf.subject_id = s.id
    LEFT JOIN user u ON sf.teacher_username = u.username
    LEFT JOIN teacher t ON sf.teacher_username = t.username
    LEFT JOIN noclass_teacher nt ON sf.teacher_username = nt.username
    WHERE sf.grade_id = :grade_id
    ORDER BY sf.feedback_date DESC
");
$stmt->execute(['grade_id' => $student_grade_id]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student: View Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        .feedback-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .feedback-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        .feedback-card h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .feedback-card p {
            margin: 5px 0;
        }
        .feedback-card .feedback-content {
            margin-top: 10px;
            font-style: italic;
            color: #555;
        }
        .feedback-card .feedback-date {
            margin-top: 10px;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <h1>Student: View Feedback</h1>
    
    <h2>Grade: <?= htmlspecialchars($grade['grade_name']) ?></h2>

    <div class="feedback-container">
        <?php if (!empty($feedbacks)): ?>
            <?php foreach ($feedbacks as $feedback): ?>
                <div class="feedback-card">
                    <h3>Subject: <?= htmlspecialchars($feedback['subject_name']) ?></h3>
                    
                    <!-- Display teacher name or handle based on their role -->
                    <p><strong>Teacher:</strong> 
                        <?php 
                            if ($feedback['role'] == 'Teacher') {
                                echo htmlspecialchars($feedback['full_name']);
                            } elseif ($feedback['role'] == 'NoClass_Teacher') {
                                echo htmlspecialchars($feedback['noclass_teacher_name']);
                            }
                        ?>
                    </p>

                    <div class="feedback-content">
                        <strong>Feedback:</strong><br>
                        <?= nl2br(htmlspecialchars($feedback['feedback'])) ?>
                    </div>
                    <div class="feedback-date">
                        <strong>Date:</strong> <?= htmlspecialchars($feedback['feedback_date']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No feedback available for your grade.</p>
        <?php endif; ?>
    </div>
</body>
</html>

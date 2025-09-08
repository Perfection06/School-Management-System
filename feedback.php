<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}


include ("db_connection.php");

// Fetch all grades to populate the grade selector dropdown
$stmt = $pdo->prepare("SELECT * FROM grades");
$stmt->execute();
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle grade selection
$selected_grade_id = null;
$feedbacks = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_id'])) {
    $selected_grade_id = $_POST['grade_id'];

    // Fetch feedback for the selected grade along with the teacher's name
    $stmt = $pdo->prepare("
        SELECT 
            feedbacks.feedback, 
            feedbacks.feedback_date, 
            teacher.full_name AS teacher_name
        FROM 
            feedbacks
        INNER JOIN 
            teacher ON feedbacks.teacher_username = teacher.username
        WHERE 
            feedbacks.grade_id = :grade_id
    ");
    $stmt->execute(['grade_id' => $selected_grade_id]);
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form {
            margin-bottom: 20px;
            text-align: center;
        }
        select, button {
            padding: 10px;
            margin: 5px;
            font-size: 16px;
        }
        .feedback-card {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .feedback-card h3 {
            margin: 0;
            color: #4CAF50;
            font-size: 20px;
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
    <h2>View Feedback</h2>

    <!-- Grade Selection Form -->
    <form method="POST">
        <label for="grade">Select Grade:</label>
        <select name="grade_id" id="grade" required>
            <option value="">-- Select Grade --</option>
            <?php foreach ($grades as $grade): ?>
                <option value="<?= htmlspecialchars($grade['id']) ?>" <?= $selected_grade_id == $grade['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($grade['grade_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">View Feedback</button>
    </form>

    <!-- Feedback Display -->
    <?php if ($feedbacks): ?>
        <?php foreach ($feedbacks as $feedback): ?>
            <div class="feedback-card">
                <h3>Teacher: <?= htmlspecialchars($feedback['teacher_name']) ?></h3>
                <p class="date">Feedback Date: <?= htmlspecialchars($feedback['feedback_date']) ?></p>
                <div class="content"><?= nl2br(htmlspecialchars($feedback['feedback'])) ?></div>
            </div>
        <?php endforeach; ?>
    <?php elseif ($selected_grade_id !== null): ?>
        <p class="no-feedback">No feedback found for the selected grade.</p>
    <?php endif; ?>
</body>
</html>

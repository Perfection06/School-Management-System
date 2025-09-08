<?php
session_start();
include("db_connection.php");


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

$teacher_username = $_SESSION['user']['username'];

// Fetch the teacher's assigned grade
$stmt = $pdo->prepare("SELECT g.id AS grade_id, g.grade_name
                       FROM grades g
                       INNER JOIN teacher t ON t.grade_id = g.id
                       WHERE t.username = :teacher_username");
$stmt->execute(['teacher_username' => $teacher_username]);
$grade = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$grade) {
    echo "<p>No grade assigned to you. Please contact the administrator.</p>";
    exit;
}

// Fetch existing feedback for this teacher and grade, if any
$stmt = $pdo->prepare("SELECT feedback FROM feedbacks WHERE teacher_username = :teacher_username AND grade_id = :grade_id");
$stmt->execute(['teacher_username' => $teacher_username, 'grade_id' => $grade['grade_id']]);
$existing_feedback = $stmt->fetchColumn();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $feedback_content = $_POST['feedback_content'];

    if (!empty($feedback_content)) {
        if ($existing_feedback) {
            // Update the existing feedback
            $stmt = $pdo->prepare("UPDATE feedbacks SET feedback = :feedback WHERE teacher_username = :teacher_username AND grade_id = :grade_id");
            $stmt->execute([
                'feedback' => $feedback_content,
                'teacher_username' => $teacher_username,
                'grade_id' => $grade['grade_id']
            ]);

            echo "<script>alert('Feedback updated successfully!');</script>";
        } else {
            // Insert new feedback
            $stmt = $pdo->prepare("INSERT INTO feedbacks (teacher_username, grade_id, feedback, feedback_date)
                                   VALUES (:teacher_username, :grade_id, :feedback, :feedback_date)");
            $stmt->execute([
                'teacher_username' => $teacher_username,
                'grade_id' => $grade['grade_id'],
                'feedback' => $feedback_content,
                'feedback_date' => date('Y-m-d')
            ]);

            echo "<script>alert('Feedback added successfully!');</script>";
        }
    } else {
        echo "<script>alert('Please provide feedback before submitting.');</script>";
    }

    // Refresh the page to reflect updated feedback
    header("Location: feedback.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
        }
        form {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        label {
            font-size: 16px;
            font-weight: bold;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<h2>Submit or Update Feedback</h2>

<form method="POST" action="feedback.php">
    <p><strong>Grade:</strong> <?php echo htmlspecialchars($grade['grade_name']); ?></p>

    <label for="feedback_content">Feedback:</label>
    <textarea name="feedback_content" id="feedback_content" rows="6" placeholder="Enter your feedback..."><?php echo htmlspecialchars($existing_feedback); ?></textarea>

    <button type="submit" name="submit_feedback">Submit Feedback</button>
</form>

</body>
</html>

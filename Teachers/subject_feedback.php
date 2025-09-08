<?php
session_start();
include("db_connection.php");

// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

// Fetch teacher username from session
$teacher_username = $_SESSION['user']['username'];

// Fetch the teacher's details
$stmt = $pdo->prepare("SELECT teaching_classes, subject_id FROM teacher WHERE username = :username");
$stmt->execute(['username' => $teacher_username]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    echo json_encode(['error' => 'Teacher details not found']);
    exit;
}

$teaching_classes = json_decode($teacher['teaching_classes'], true);

// Fetch grades
$stmt = $pdo->prepare("SELECT id, grade_name FROM grades WHERE id IN (" . implode(',', array_map('intval', $teaching_classes)) . ")");
$stmt->execute();
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle AJAX request for existing feedback
if (isset($_GET['action']) && $_GET['action'] === 'get_feedback') {
    $grade_id = $_GET['grade_id'];
    $subject_id = $teacher['subject_id'];

    $stmt = $pdo->prepare("
        SELECT feedback 
        FROM subject_feedbacks 
        WHERE teacher_username = :teacher_username AND grade_id = :grade_id AND subject_id = :subject_id
    ");
    $stmt->execute([
        'teacher_username' => $teacher_username,
        'grade_id' => $grade_id,
        'subject_id' => $subject_id,
    ]);
    $existing_feedback = $stmt->fetchColumn();
    echo json_encode(['feedback' => $existing_feedback ?? '']);
    exit;
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $grade_id = $_POST['grade_id'];
    $feedback = $_POST['feedback'];
    $feedback_date = date('Y-m-d');
    $subject_id = $teacher['subject_id'];

    // Check if feedback already exists
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM subject_feedbacks 
        WHERE teacher_username = :teacher_username AND grade_id = :grade_id AND subject_id = :subject_id
    ");
    $stmt->execute([
        'teacher_username' => $teacher_username,
        'grade_id' => $grade_id,
        'subject_id' => $subject_id,
    ]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        // Update existing feedback
        $stmt = $pdo->prepare("
            UPDATE subject_feedbacks
            SET feedback = :feedback, feedback_date = :feedback_date
            WHERE teacher_username = :teacher_username 
              AND grade_id = :grade_id 
              AND subject_id = :subject_id
        ");
        $stmt->execute([
            'feedback' => $feedback,
            'feedback_date' => $feedback_date,
            'teacher_username' => $teacher_username,
            'grade_id' => $grade_id,
            'subject_id' => $subject_id,
        ]);
    } else {
        // Insert new feedback
        $stmt = $pdo->prepare("
            INSERT INTO subject_feedbacks (teacher_username, grade_id, subject_id, feedback, feedback_date)
            VALUES (:teacher_username, :grade_id, :subject_id, :feedback, :feedback_date)
        ");
        $stmt->execute([
            'teacher_username' => $teacher_username,
            'grade_id' => $grade_id,
            'subject_id' => $subject_id,
            'feedback' => $feedback,
            'feedback_date' => $feedback_date,
        ]);
    }

    echo json_encode(['message' => 'Feedback successfully saved!']);
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        form {
            background-color: #fff;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-top: 0;
        }
        label {
            display: block;
            margin: 15px 0 5px;
        }
        select, textarea, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #28a745;
            color: #fff;
            border: none;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
    <script>
        async function loadFeedback(gradeId) {
            if (!gradeId) {
                document.getElementById('feedback').value = '';
                return;
            }

            const response = await fetch(`subject_feedback.php?action=get_feedback&grade_id=${gradeId}`);
            const data = await response.json();
            document.getElementById('feedback').value = data.feedback || '';
        }

        async function saveFeedback(event) {
            event.preventDefault();
            const gradeId = document.getElementById('grade_id').value;
            const feedback = document.getElementById('feedback').value;

            const formData = new FormData();
            formData.append('grade_id', gradeId);
            formData.append('feedback', feedback);

            const response = await fetch('subject_feedback.php', {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();
            alert(data.message);
        }
    </script>
</head>
<body>
<?php include('navbar.php'); ?>
    <form id="feedbackForm">
        <h2>Provide Feedback</h2>
        <label for="grade_id">Select Grade:</label>
        <select name="grade_id" id="grade_id" onchange="loadFeedback(this.value)" required>
            <option value="">-- Select Grade --</option>
            <?php foreach ($grades as $grade): ?>
                <option value="<?= htmlspecialchars($grade['id']) ?>"><?= htmlspecialchars($grade['grade_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="feedback">Feedback:</label>
        <textarea name="feedback" id="feedback" required></textarea>

        <button type="submit" onclick="saveFeedback(event)">Save Feedback</button>
    </form>
</body>
</html>

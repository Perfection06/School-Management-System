<?php
session_start();
include("db_connection.php");

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Fetch grades
$stmt = $pdo->prepare("SELECT id, grade_name FROM grades");
$stmt->execute();
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subjects
$stmt = $pdo->prepare("SELECT id, subject_name FROM subjects");
$stmt->execute();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch feedback based on selected filters
$filterGrade = $_GET['grade_id'] ?? null;
$filterSubject = $_GET['subject_id'] ?? null;

$sql = "
    SELECT sf.id, g.grade_name, s.subject_name, sf.teacher_username, sf.feedback, sf.feedback_date, u.role, t.full_name, nt.full_name AS noclass_teacher_name
    FROM subject_feedbacks sf
    JOIN grades g ON sf.grade_id = g.id
    JOIN subjects s ON sf.subject_id = s.id
    LEFT JOIN user u ON sf.teacher_username = u.username
    LEFT JOIN teacher t ON sf.teacher_username = t.username
    LEFT JOIN noclass_teacher nt ON sf.teacher_username = nt.username
";

$conditions = [];
$params = [];

if ($filterGrade) {
    $conditions[] = "sf.grade_id = :grade_id";
    $params['grade_id'] = $filterGrade;
}

if ($filterSubject) {
    $conditions[] = "sf.subject_id = :subject_id";
    $params['subject_id'] = $filterSubject;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY sf.feedback_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin View Feedback</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        .filter-form {
            margin-bottom: 20px;
        }
        .filter-form select {
            margin-right: 10px;
            padding: 10px;
        }
        .filter-form button {
            padding: 10px 15px;
            font-size: 16px;
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
    <h1>Admin: View Feedback</h1>

    <form class="filter-form" method="GET">
        <label for="grade_id">Grade:</label>
        <select name="grade_id" id="grade_id">
            <option value="">-- All Grades --</option>
            <?php foreach ($grades as $grade): ?>
                <option value="<?= htmlspecialchars($grade['id']) ?>" <?= $filterGrade == $grade['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($grade['grade_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="subject_id">Subject:</label>
        <select name="subject_id" id="subject_id">
            <option value="">-- All Subjects --</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= htmlspecialchars($subject['id']) ?>" <?= $filterSubject == $subject['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($subject['subject_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Filter</button>
    </form>

    <div class="feedback-container">
        <?php if (!empty($feedbacks)): ?>
            <?php foreach ($feedbacks as $feedback): ?>
                <div class="feedback-card">
                    <h3>Grade: <?= htmlspecialchars($feedback['grade_name']) ?></h3>
                    <p><strong>Subject:</strong> <?= htmlspecialchars($feedback['subject_name']) ?></p>
                    
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
            <p>No feedback found for the selected filters.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
include("db_connection.php");
session_start();

// Verify the student session
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['user']['username']);

// Fetch student's grade_id using username
$query = "SELECT grade_id FROM Students WHERE username = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$username]);
$studentGrade = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$studentGrade) {
    echo "No grade found for this student.";
    exit;
}

$gradeId = $studentGrade['grade_id'];

// Fetch all tests and results for the student's grade
$testsQuery = "
    SELECT  
        t.type, 
        s.subject_name, 
        CASE 
            WHEN t.teacher_username IS NOT NULL THEN CONCAT('Teacher: ', teacher.full_name)
            WHEN t.noclass_teacher_username IS NOT NULL THEN CONCAT('Sub Teacher: ', noclass_teacher.full_name)
            WHEN t.staff_username IS NOT NULL THEN CONCAT('Staff: ', staff.full_name)
            ELSE 'Unknown'
        END AS creator_info,
        tm.marks_obtained, 
        tm.rank
    FROM tests t
    LEFT JOIN subjects s ON t.subject_id = s.id
    LEFT JOIN teacher ON t.teacher_username = teacher.username
    LEFT JOIN noclass_teacher ON t.noclass_teacher_username = noclass_teacher.username
    LEFT JOIN staff ON t.staff_username = staff.username
    LEFT JOIN test_marks tm ON t.id = tm.test_id AND tm.student_username = ?
    WHERE t.grade_id = ?
    ORDER BY t.test_date DESC
";


$testsStmt = $pdo->prepare($testsQuery);
$testsStmt->execute([$username, $gradeId]);
$tests = $testsStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .test-card {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background-color: #f9f9f9;
        }

        .test-card h2 {
            margin: 0;
            font-size: 18px;
            color: #007bff;
        }

        .test-card p {
            margin: 5px 0;
            font-size: 16px;
            color: #333;
        }

        .marks-rank {
            margin-top: 10px;
            font-weight: bold;
        }

        .no-results {
            text-align: center;
            font-size: 18px;
            color: #999;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container">
    <h1>Your Test Results</h1>
    <?php if (empty($tests)): ?>
        <p class="no-results">No test results available for your grade.</p>
    <?php else: ?>
        <?php foreach ($tests as $test): ?>
            <div class="test-card">
                <h2><?= htmlspecialchars($test['type']) . " Test - " . htmlspecialchars($test['subject_name']); ?></h2>
                <p><?= htmlspecialchars($test['creator_info']); ?></p>
                <?php if ($test['marks_obtained'] !== null): ?>
                    <div class="marks-rank">
                        Marks: <?= htmlspecialchars($test['marks_obtained']); ?> &nbsp;&nbsp;&nbsp;
                        Rank: <?= htmlspecialchars($test['rank']); ?>
                    </div>
                <?php else: ?>
                    <p class="no-results">Result not published for this test yet.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>

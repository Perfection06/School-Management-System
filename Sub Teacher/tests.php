<?php
session_start();

// Check if the user is logged in and is a 'NoClass_Teacher'
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'NoClass_Teacher') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connection.php';

// Fetch No Class Teacher's assigned subjects and classes
$noclassTeacherUsername = $_SESSION['user']['username']; // Assuming the No Class Teacher's username is stored in session

// Fetch teaching_classes (JSON) and subject_id from the noclass_teacher's table
$query = "SELECT teaching_classes, subject_id FROM noclass_teacher WHERE username = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$noclassTeacherUsername]);
$noclassTeacherData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$noclassTeacherData) {
    die("No Class Teacher not found.");
}

// Decode the teaching_classes JSON to get grade IDs
$teachingClasses = json_decode($noclassTeacherData['teaching_classes'], true);
$subjectId = $noclassTeacherData['subject_id'];

if (empty($teachingClasses)) {
    die("No grades assigned to this No Class Teacher.");
}

// Fetch grade names based on teaching_classes
$placeholders = str_repeat('?,', count($teachingClasses) - 1) . '?';
$query = "SELECT id AS grade_id, grade_name FROM grades WHERE id IN ($placeholders)";
$stmt = $pdo->prepare($query);
$stmt->execute($teachingClasses);
$assignedGrades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle test creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $gradeId = $_POST['grade_id'];
    $testDate = $_POST['test_date'];
    $publishDate = $_POST['publish_date'];

    // Validate input
    if (empty($type) || empty($gradeId) || empty($testDate) || empty($publishDate)) {
        $error = "All fields are required.";
    } elseif ($testDate < date('Y-m-d')) {
        $error = "Test date cannot be in the past.";
    } elseif ($publishDate < $testDate) {
        $error = "Publish date must be on or after the test date.";
    } else {
        // Insert test into the database with only the No Class Teacher's username and the others set to null
        $stmt = $pdo->prepare("
            INSERT INTO tests (type, grade_id, subject_id, teacher_username, noclass_teacher_username, staff_username, test_date, publish_date)
            VALUES (?, ?, ?, NULL, ?, NULL, ?, ?)
        ");
        if ($stmt->execute([$type, $gradeId, $subjectId, $noclassTeacherUsername, $testDate, $publishDate])) {
            echo "<script>
                alert('Test created successfully!');
                window.location.href = 'tests.php';
            </script>";
        } else {
            echo "<script>
                alert('Failed to create test. Please try again.');
                window.location.href = 'tests.php';
            </script>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
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

        form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        form input, form select, form button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        form button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        form button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            font-weight: bold;
        }

        .success {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container">
    <h1>Create Test</h1>

    <form method="POST">
        <label for="type">Test Type</label>
        <select id="type" name="type" required>
            <option value="Monthly Test">Monthly Test</option>
            <option value="Unit Test">Unit Test</option>
        </select>

        <label for="grade_id">Grade</label>
        <select id="grade_id" name="grade_id" required>
            <option value="" disabled selected>Select a Grade</option>
            <?php foreach ($assignedGrades as $grade): ?>
                <option value="<?= $grade['grade_id']; ?>"><?= htmlspecialchars($grade['grade_name']); ?></option>
            <?php endforeach; ?>
        </select>

        <label for="test_date">Test Date</label>
        <input type="date" id="test_date" name="test_date" required>

        <label for="publish_date">Result Publish Date</label>
        <input type="date" id="publish_date" name="publish_date" required>

        <button type="submit">Create Test</button>
    </form>
</div>
</body>
</html>

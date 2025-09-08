<?php
session_start();

// Check if the user is logged in and is a 'Staff'
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Staff') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connection.php';

// Fetch Staff's assigned subjects
$staffUsername = $_SESSION['user']['username']; // Assuming the Staff's username is stored in session

// Fetch subjects assigned to this staff from the staff table
$query = "SELECT position FROM staff WHERE username = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$staffUsername]);
$staffData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$staffData) {
    die("Staff not found.");
}

// Fetch all subjects available
$query = "SELECT id AS subject_id, subject_name FROM subjects";
$stmt = $pdo->prepare($query);
$stmt->execute();
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch grade names based on subject selection
if (isset($_POST['subject_id'])) {
    $subjectId = $_POST['subject_id'];

    // Get grades assigned to this subject
    $query = "SELECT g.id AS grade_id, g.grade_name FROM grades g
              JOIN grade_subject gs ON gs.grade_id = g.id
              WHERE gs.subject_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$subjectId]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $grades = [];
}

// Handle test creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $gradeId = $_POST['grade_id'] ?? '';
    $subjectId = $_POST['subject_id'];
    $testDate = $_POST['test_date'];
    $publishDate = $_POST['publish_date'];

    // Validate input
    if (empty($type) || empty($gradeId) || empty($subjectId) || empty($testDate) || empty($publishDate)) {
        $error = "All fields are required.";
    } elseif ($testDate < date('Y-m-d')) {
        $error = "Test date cannot be in the past.";
    } elseif ($publishDate < $testDate) {
        $error = "Publish date must be on or after the test date.";
    } else {
        // Insert test into the database with only the Staff's username and the others set to null
        $stmt = $pdo->prepare("
            INSERT INTO tests (type, grade_id, subject_id, teacher_username, noclass_teacher_username, staff_username, test_date, publish_date)
            VALUES (?, ?, ?, NULL, NULL, ?, ?, ?)
        ");
        if ($stmt->execute([$type, $gradeId, $subjectId, $staffUsername, $testDate, $publishDate])) {
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

    <label for="subject_id">Subject</label>
    <select id="subject_id" name="subject_id" required onchange="this.form.submit()">
        <option value="" disabled selected>Select a Subject</option>
        <?php foreach ($subjects as $subject): ?>
            <option value="<?= $subject['subject_id']; ?>" <?= isset($_POST['subject_id']) && $_POST['subject_id'] == $subject['subject_id'] ? 'selected' : ''; ?>>
                <?= htmlspecialchars($subject['subject_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (!empty($grades)): ?>
        <label for="grade_id">Grade</label>
        <select id="grade_id" name="grade_id" required>
            <option value="" disabled selected>Select a Grade</option>
            <?php foreach ($grades as $grade): ?>
                <option value="<?= $grade['grade_id']; ?>" <?= isset($_POST['grade_id']) && $_POST['grade_id'] == $grade['grade_id'] ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($grade['grade_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <label for="test_date">Test Date</label>
    <input type="date" id="test_date" name="test_date" required>

    <label for="publish_date">Result Publish Date</label>
    <input type="date" id="publish_date" name="publish_date" required>

    <button type="submit">Create Test</button>
</form>

</div>
</body>
</html>

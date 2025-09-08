<?php
session_start();

// Check if user is logged in and is a Teacher
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connection.php';

// Fetch tests created by the logged-in teacher
$teacherUsername = $_SESSION['user']['username']; // Assuming teacher's username is stored in session
$testsQuery = "
    SELECT t.id, t.type, g.grade_name, t.test_date, t.publish_date
    FROM tests t
    JOIN grades g ON t.grade_id = g.id
    WHERE t.teacher_username = ?
";
$testsStmt = $pdo->prepare($testsQuery);
$testsStmt->execute([$teacherUsername]);
$tests = $testsStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for marks entry
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testId = $_POST['test_id'];
    $marksData = $_POST['marks']; // Array: student_username => marks_obtained
    $ranksData = $_POST['ranks']; // Array: student_username => rank

    $pdo->beginTransaction();
    try {
        foreach ($marksData as $studentUsername => $marks) {
            $rank = $ranksData[$studentUsername] ?? null;

            // Check if a record already exists
            $checkStmt = $pdo->prepare("SELECT id FROM test_marks WHERE test_id = ? AND student_username = ?");
            $checkStmt->execute([$testId, $studentUsername]);

            if ($checkStmt->fetch()) {
                // Update existing record
                $updateStmt = $pdo->prepare("
                    UPDATE test_marks 
                    SET marks_obtained = ?, rank = ? 
                    WHERE test_id = ? AND student_username = ?
                ");
                $updateStmt->execute([$marks, $rank, $testId, $studentUsername]);
            } else {
                // Insert new record
                $insertStmt = $pdo->prepare("
                    INSERT INTO test_marks (test_id, student_username, marks_obtained, rank)
                    VALUES (?, ?, ?, ?)
                ");
                $insertStmt->execute([$testId, $studentUsername, $marks, $rank]);
            }
        }

        $pdo->commit();
        echo "<script>
            alert('Marks and ranks have been saved successfully!');
            window.location.href = 'test_marks.php';
        </script>";
        exit; // Ensure no further code execution
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>
            alert('Failed to save marks. Please try again.');
            window.location.href = 'test_marks.php';
        </script>";
        exit; // Ensure no further code execution
    }
}

// Fetch students for the selected test's grade
$students = [];
$disableForm = false;
if (isset($_GET['test_id'])) {
    $testId = $_GET['test_id'];
    $studentsQuery = "
        SELECT s.username, s.name, tm.marks_obtained, tm.rank, t.publish_date
        FROM Students s
        LEFT JOIN test_marks tm ON s.username = tm.student_username AND tm.test_id = ?
        JOIN tests t ON s.grade_id = t.grade_id
        WHERE t.id = ?
    ";
    $studentsStmt = $pdo->prepare($studentsQuery);
    $studentsStmt->execute([$testId, $testId]);
    $students = $studentsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get the publish date for the selected test
    $testPublishDate = $students[0]['publish_date'] ?? null;
    if ($testPublishDate) {
        // Convert publish date to DateTime object and add 1 day
        $publishDate = new DateTime($testPublishDate);
        $publishDate->modify('+1 day');
        $currentDate = new DateTime();

        // If current date is after the publish_date + 1 day, disable the form
        if ($currentDate > $publishDate) {
            $disableForm = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Test Marks</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        table th {
            background-color: #f2f2f2;
        }

        label, select, button {
            display: block;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            opacity: 0.9;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }

        /* Print styles */
        @media print {
            body * {
                visibility: hidden;
            }

            .printable-area, .printable-area * {
                visibility: visible;
            }

            .printable-area button {
                display: none; 
            }

            .printable-area {
                position: absolute;
                left: 0;
                top: 0;
            }
        }
    </style>
    <script>
        // Function to print only the table
        function printTable() {
            window.print();
        }
    </script>
</head>
<body>
<div class="container">
    <h1>Enter Test Marks</h1>

    <form method="GET">
        <label for="test_id">Select Test</label>
        <select id="test_id" name="test_id" onchange="this.form.submit()" <?= $disableForm ? 'disabled' : ''; ?>>
            <option value="" disabled selected>Select a Test</option>
            <?php foreach ($tests as $test): ?>
                <option value="<?= $test['id']; ?>" <?= (isset($_GET['test_id']) && $_GET['test_id'] == $test['id']) ? 'selected' : ''; ?> <?= $disableForm ? 'disabled' : ''; ?>>
                    <?= htmlspecialchars($test['type'] . ' - ' . $test['grade_name'] . ' (' . $test['test_date'] . ')'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (!empty($students)): ?>
        <div class="printable-area">
            <form method="POST">
                <input type="hidden" name="test_id" value="<?= htmlspecialchars($testId); ?>">
                <table>
                    <thead>
                    <tr>
                        <th>Student Username</th>
                        <th>Student Name</th>
                        <th>Marks Obtained</th>
                        <th>Rank</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['username']); ?></td>
                            <td><?= htmlspecialchars($student['name']); ?></td>
                            <td>
                                <input type="number" name="marks[<?= htmlspecialchars($student['username']); ?>]" 
                                       value="<?= htmlspecialchars($student['marks_obtained'] ?? ''); ?>" 
                                       step="0.01" required <?= $disableForm ? 'disabled' : ''; ?>>
                            </td>
                            <td>
                                <input type="number" name="ranks[<?= htmlspecialchars($student['username']); ?>]" 
                                       value="<?= htmlspecialchars($student['rank'] ?? ''); ?>" <?= $disableForm ? 'disabled' : ''; ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" <?= $disableForm ? 'disabled' : ''; ?>>Save Marks</button>
            </form>
        </div>
        <button onclick="printTable()">Print Table</button>
    <?php endif; ?>
</div>
</body>
</html>

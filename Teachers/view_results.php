<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

include ("db_connection.php");

// Get the assigned class ID
$assigned_class_id = $_SESSION['assigned_class_id'];

// Fetch exams for this teacher's class
$exam_stmt = $pdo->prepare("SELECT * FROM Exams WHERE class_id = :class_id");
$exam_stmt->execute(['class_id' => $assigned_class_id]);
$exams = $exam_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if an exam is selected
$selected_exam_id = isset($_GET['exam_id']) ? $_GET['exam_id'] : null;

// Initialize exam title
$exam_title = null;

// Fetch results for the assigned class if an exam is selected
if ($selected_exam_id) {
    // Fetch the selected exam title
    $exam_title_stmt = $pdo->prepare("SELECT exam_title FROM Exams WHERE id = :exam_id");
    $exam_title_stmt->execute(['exam_id' => $selected_exam_id]);
    $exam_title = $exam_title_stmt->fetchColumn();

    $result_stmt = $pdo->prepare("
        SELECT s.id AS student_id, s.name AS student_name, r.rank, e.exam_title 
        FROM Students s
        LEFT JOIN Marks m ON s.id = m.student_id AND m.exam_id = :exam_id
        LEFT JOIN Ranks r ON s.id = r.student_id AND r.exam_id = :exam_id
        JOIN Exams e ON e.id = m.exam_id
        WHERE e.id = :exam_id AND s.grade = 
              (SELECT grade_name FROM Grades WHERE id = :class_id)
        GROUP BY s.id, s.name, e.exam_title
    ");
    $result_stmt->execute(['exam_id' => $selected_exam_id, 'class_id' => $assigned_class_id]);
    $results = $result_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $results = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Results</title>
    <style>
        /* Basic styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f8;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        /* Container */
        .container {
            width: 90%;
            max-width: 800px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        /* Header */
        h2 {
            color: #2c3e50;
            font-size: 1.5em;
            margin-bottom: 1em;
            text-align: center;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.3em;
        }

        /* Dropdown */
        .form-group {
            margin-bottom: 20px;
            text-align: center;
        }

        select {
            padding: 10px;
            font-size: 1em;
            border-radius: 4px;
            border: 1px solid #ddd;
            width: 100%;
            max-width: 300px;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 1em;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }

        td {
            background-color: #f9f9f9;
        }

        /* Row styling */
        tr:nth-child(even) td {
            background-color: #eef4f8;
        }

        /* No results message */
        .no-results {
            text-align: center;
            color: #666;
            font-style: italic;
            margin-top: 20px;
        }

        /* Responsive styling */
        @media (max-width: 600px) {
            h2 {
                font-size: 1.2em;
            }
            th, td {
                font-size: 0.9em;
                padding: 8px;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container">
    <h2>Select an Exam to View Results</h2>

    <!-- Display exams -->
    <form method="GET" action="" class="form-group">
        <label for="exam">Choose an exam:</label>
        <select id="exam" name="exam_id" onchange="this.form.submit()">
            <option value="">Select an exam</option>
            <?php foreach ($exams as $exam): ?>
                <option value="<?= htmlspecialchars($exam['id']) ?>" <?= $selected_exam_id == $exam['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($exam['exam_title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- If an exam is selected, show the results -->
    <?php if ($selected_exam_id && $exam_title): ?>
        <h2>Results for <?= htmlspecialchars($exam_title) ?></h2>
        <table>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Exam Title</th>
                <th>Rank</th>
                <th>Action</th>
            </tr>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?= htmlspecialchars($result['student_id']) ?></td>
                    <td><?= htmlspecialchars($result['student_name']) ?></td>
                    <td><?= htmlspecialchars($result['exam_title']) ?></td>
                    <td><?= htmlspecialchars($result['rank']) ?></td>
                    <td>
                        <form method="POST" action="view_student_marks.php" style="display:inline;">
                            <input type="hidden" name="student_id" value="<?= htmlspecialchars($result['student_id']) ?>">
                            <input type="hidden" name="exam_id" value="<?= htmlspecialchars($selected_exam_id) ?>">
                            <button type="submit">View Marks</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p class="no-results">Please select an exam to view results.</p>
    <?php endif; ?>
</div>
</body>
</html>

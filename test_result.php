<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connection.php';

// Fetch subjects
$subjectsQuery = "SELECT id, subject_name FROM subjects";
$subjectsStmt = $pdo->prepare($subjectsQuery);
$subjectsStmt->execute();
$subjects = $subjectsStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle subject and grade selection
$grades = [];
$groupedResults = [];
if (isset($_GET['subject_id'])) {
    $subjectId = $_GET['subject_id'];

    // Fetch grades that have tests for the selected subject
    $gradesQuery = "
        SELECT DISTINCT g.id, g.grade_name
        FROM grades g
        JOIN tests t ON g.id = t.grade_id
        WHERE t.subject_id = ?
    ";
    $gradesStmt = $pdo->prepare($gradesQuery);
    $gradesStmt->execute([$subjectId]);
    $grades = $gradesStmt->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_GET['grade_id'])) {
        $gradeId = $_GET['grade_id'];

        // Fetch test results grouped by creator
        $groupedResultsQuery = "
            SELECT t.type AS test_type, g.grade_name, s.subject_name, 
                   COALESCE(tc.full_name, nc.full_name, st.full_name) AS creator_name,
                   CASE 
                       WHEN tc.username IS NOT NULL THEN 'Teacher'
                       WHEN nc.username IS NOT NULL THEN 'No Class Teacher'
                       WHEN st.username IS NOT NULL THEN 'Staff Teacher'
                   END AS creator_role,
                   tm.student_username, std.name AS student_name, tm.marks_obtained, tm.rank
            FROM tests t
            LEFT JOIN grades g ON t.grade_id = g.id
            LEFT JOIN subjects s ON t.subject_id = s.id
            LEFT JOIN teacher tc ON t.teacher_username = tc.username
            LEFT JOIN noclass_teacher nc ON t.noclass_teacher_username = nc.username
            LEFT JOIN staff st ON t.staff_username = st.username
            LEFT JOIN test_marks tm ON t.id = tm.test_id
            LEFT JOIN Students std ON tm.student_username = std.username
            WHERE t.subject_id = ? AND t.grade_id = ?
            ORDER BY creator_name, tm.rank ASC
        ";
        $groupedResultsStmt = $pdo->prepare($groupedResultsQuery);
        $groupedResultsStmt->execute([$subjectId, $gradeId]);
        $allResults = $groupedResultsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Group results by creator
        foreach ($allResults as $result) {
            $creatorKey = $result['creator_name'] . " - " . $result['creator_role'];
            if (!isset($groupedResults[$creatorKey])) {
                $groupedResults[$creatorKey] = [
                    'test_type' => $result['test_type'],
                    'grade_name' => $result['grade_name'],
                    'subject_name' => $result['subject_name'],
                    'results' => []
                ];
            }
            $groupedResults[$creatorKey]['results'][] = $result;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Test Results</title>
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

        label, select, button {
            display: block;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        table th {
            background-color: #f2f2f2;
        }

        .creator-section {
            margin-top: 30px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f7f7f7;
        }

        .print-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            text-align: center;
        }

        .print-btn:hover {
            background-color: #45a049;
        }

        /* Hide print button when printing */
        @media print {
            .print-btn {
                display: none !important; /* Hide print button in print view */
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container">
    <h1>View Test Results</h1>

    <form method="GET">
        <label for="subject_id">Select Subject</label>
        <select id="subject_id" name="subject_id" onchange="this.form.submit()">
            <option value="" disabled selected>Select a subject</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['id']; ?>" <?= (isset($_GET['subject_id']) && $_GET['subject_id'] == $subject['id']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($subject['subject_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (!empty($grades)): ?>
        <form method="GET">
            <input type="hidden" name="subject_id" value="<?= htmlspecialchars($subjectId); ?>">
            <label for="grade_id">Select Grade</label>
            <select id="grade_id" name="grade_id" onchange="this.form.submit()">
                <option value="" disabled selected>Select a grade</option>
                <?php foreach ($grades as $grade): ?>
                    <option value="<?= $grade['id']; ?>" <?= (isset($_GET['grade_id']) && $_GET['grade_id'] == $grade['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($grade['grade_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php endif; ?>

    <?php if (!empty($groupedResults)): ?>
        <?php foreach ($groupedResults as $creator => $data): ?>
            <div class="creator-section" id="result-<?= $creator; ?>">
                <h2><?= htmlspecialchars($data['test_type'] . " - " . $data['grade_name']); ?></h2>
                <h3><?= htmlspecialchars($data['subject_name']); ?></h3>
                <p><strong><?= htmlspecialchars($creator); ?></strong></p>
                <table>
                    <thead>
                    <tr>
                        <th>Student Username</th>
                        <th>Student Name</th>
                        <th>Marks</th>
                        <th>Rank</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data['results'] as $result): ?>
                        <tr>
                            <td><?= htmlspecialchars($result['student_username']); ?></td>
                            <td><?= htmlspecialchars($result['student_name']); ?></td>
                            <td><?= htmlspecialchars($result['marks_obtained']); ?></td>
                            <td><?= htmlspecialchars($result['rank']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Print Button -->
            <button class="print-btn" onclick="printResult('result-<?= $creator; ?>')">Print Result</button>
        <?php endforeach; ?>
    <?php elseif (isset($_GET['grade_id'])): ?>
        <p>No results found for the selected grade.</p>
    <?php endif; ?>
</div>

<script>
    function printResult(resultId) {
        // Hide the print button before opening the print window
        var printButton = document.querySelector('.print-btn');
        printButton.style.display = 'none'; // Hide the button
        
        var printContent = document.getElementById(resultId);
        var printWindow = window.open('', '', 'height=600, width=800');
        printWindow.document.write('<html><head><title>Print Result</title>');
        printWindow.document.write('<style> body { font-family: Arial, sans-serif; } table { width: 100%; border-collapse: collapse; } table th, table td { border: 1px solid #ddd; padding: 8px; text-align: center; } table th { background-color: #f2f2f2; }</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContent.innerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        
        // Wait for the content to load before printing
        printWindow.onload = function () {
            printWindow.print();
            printWindow.close();
        };
        
        // Show the print button again after printing is completed
        printButton.style.display = 'inline-block';
    }
</script>
</body>
</html>

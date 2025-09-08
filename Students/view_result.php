<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

include("db_connection.php");

// Get logged-in student's username
$username = htmlspecialchars($_SESSION['user']['username']);

// Fetch results for the logged-in student
$query = "
    SELECT e.id AS exam_id, e.title AS exam_title, e.term, sub.subject_name, sm.marks, r.rank
    FROM exams e
    LEFT JOIN exam_subjects es ON es.exam_id = e.id
    LEFT JOIN subjects sub ON es.subject_id = sub.id
    LEFT JOIN student_marks sm ON sm.exam_id = e.id AND sm.subject_id = es.subject_id
    LEFT JOIN ranks r ON r.exam_id = e.id AND r.username = ?
    WHERE sm.student_username = ?
    ORDER BY e.term, e.id, sub.subject_name
";
$stmt = $pdo->prepare($query);
$stmt->execute([$username, $username]);

// Process the fetched data
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$examData = [];

if ($results) {
    foreach ($results as $row) {
        $examId = $row['exam_id'];
        $subject = $row['subject_name'];

        if (!isset($examData[$examId])) {
            $examData[$examId] = [
                'title' => $row['exam_title'],
                'term' => $row['term'],
                'subjects' => [],
                'rank' => $row['rank'] ?? 'N/A',
            ];
        }

        $examData[$examId]['subjects'][$subject] = $row['marks'] !== null ? $row['marks'] : 'N/A';
    }
} else {
    echo "<p>No results found for your exams.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .rank {
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="container">
        <h1>My Results</h1>

        <?php if (!empty($examData)): ?>
            <?php foreach ($examData as $examId => $exam): ?>
                <h2>Exam: <?php echo htmlspecialchars($exam['title']); ?> (Term: <?php echo htmlspecialchars($exam['term']); ?>)</h2>
                <p class="rank">Rank: <?php echo htmlspecialchars($exam['rank']); ?></p>
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exam['subjects'] as $subject => $marks): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($subject); ?></td>
                                <td><?php echo htmlspecialchars($marks); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No results available.</p>
        <?php endif; ?>
    </div>
</body>
</html>

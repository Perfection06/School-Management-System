<?php
include("db_connection.php");
session_start();

// Verify teacher session
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

$username = htmlspecialchars($_SESSION['user']['username']);

// Fetch teacher's assigned grade
$query = "SELECT grade_id FROM teacher WHERE username = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$username]);
$teacherInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacherInfo) {
    echo "No assigned grade found.";
    exit;
}

$grade_id = $teacherInfo['grade_id'];

// Fetch exams for the teacher's grade
$examQuery = "SELECT * FROM exams WHERE grade_id = ? ORDER BY term, start_date";
$examStmt = $pdo->prepare($examQuery);
$examStmt->execute([$grade_id]);
$exams = $examStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch student data and their marks for the grade
$marksQuery = "
    SELECT s.username, s.name, e.id AS exam_id, e.title AS exam_title, e.term, e.publish_date, sub.subject_name, sm.marks
    FROM students s
    CROSS JOIN exams e ON e.grade_id = s.grade_id
    LEFT JOIN exam_subjects es ON es.exam_id = e.id
    LEFT JOIN subjects sub ON es.subject_id = sub.id
    LEFT JOIN student_marks sm ON sm.exam_id = e.id AND sm.subject_id = es.subject_id AND sm.student_username = s.username
    WHERE e.grade_id = ?
    ORDER BY e.term, e.id, s.username, sub.subject_name
";
$stmt = $pdo->prepare($marksQuery);
$stmt->execute([$grade_id]);
$marksData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group data by exam
$examData = [];
if ($marksData) {
    foreach ($marksData as $row) {
        $examId = $row['exam_id'];
        $username = $row['username'];
        $subject = $row['subject_name'];

        if (!isset($examData[$examId])) {
            $examData[$examId] = [
                'title' => $row['exam_title'],
                'term' => $row['term'],
                'publish_date' => $row['publish_date'],
                'students' => [],
            ];
        }

        if (!isset($examData[$examId]['students'][$username])) {
            $examData[$examId]['students'][$username] = [
                'name' => $row['name'],
                'subjects' => [],
                'total' => 0,
            ];
        }

        $marks = $row['marks'];
        $examData[$examId]['students'][$username]['subjects'][$subject] = $marks !== null ? $marks : 'N/A';
        $examData[$examId]['students'][$username]['total'] += $marks !== null ? $marks : 0;
    }
}

// Fetch existing ranks for the grade's exams
$rankQuery = "
    SELECT r.exam_id, r.username, r.rank
    FROM ranks r
    WHERE r.grade_id = ?
";
$rankStmt = $pdo->prepare($rankQuery);
$rankStmt->execute([$grade_id]);
$rankData = $rankStmt->fetchAll(PDO::FETCH_ASSOC);

// Map rank data into $examData
foreach ($rankData as $rank) {
    $examId = $rank['exam_id'];
    $username = $rank['username'];
    if (isset($examData[$examId]['students'][$username])) {
        $examData[$examId]['students'][$username]['rank'] = $rank['rank'];
    }
}

// Handle POST request for rank submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rank'])) {
    $exam_id = $_POST['exam_id'];
    $term = $_POST['term'];
    $submittedRanks = $_POST['ranks'];

    // Fetch the publish date of the exam
    $publishQuery = "SELECT publish_date FROM exams WHERE id = ?";
    $publishStmt = $pdo->prepare($publishQuery);
    $publishStmt->execute([$exam_id]);
    $publishData = $publishStmt->fetch(PDO::FETCH_ASSOC);

    $currentDate = date('Y-m-d');
    if ($publishData && $currentDate <= $publishData['publish_date']) {
        foreach ($submittedRanks as $student_username => $rank) {
            try {
                // Check if the rank record already exists
                $checkQuery = "
                    SELECT COUNT(*) FROM ranks 
                    WHERE username = ? AND grade_id = ? AND exam_id = ? AND term = ?
                ";
                $checkStmt = $pdo->prepare($checkQuery);
                $checkStmt->execute([$student_username, $grade_id, $exam_id, $term]);
                $exists = $checkStmt->fetchColumn() > 0;

                if ($exists) {
                    // Update the existing rank
                    $updateQuery = "
                        UPDATE ranks
                        SET rank = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE username = ? AND grade_id = ? AND exam_id = ? AND term = ?
                    ";
                    $updateStmt = $pdo->prepare($updateQuery);
                    $updateStmt->execute([
                        $rank,
                        $student_username,
                        $grade_id,
                        $exam_id,
                        $term
                    ]);
                } else {
                    // Insert a new rank
                    $insertQuery = "
                        INSERT INTO ranks (username, grade_id, exam_id, term, rank)
                        VALUES (?, ?, ?, ?, ?)
                    ";
                    $insertStmt = $pdo->prepare($insertQuery);
                    $insertStmt->execute([
                        $student_username,
                        $grade_id,
                        $exam_id,
                        $term,
                        $rank
                    ]);
                }
            } catch (PDOException $e) {
                echo "<script>alert('Error updating rank for student {$student_username}: " . $e->getMessage() . "');</script>";
            }
        }

        echo "<script>
            alert('Ranks for exam {$exam_id} have been successfully updated!');
            window.location.href = 'rank.php';
        </script>";
    } else {
        echo "<script>alert('Unable to update ranks. Exam publish date has passed.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Ranking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            background-color: #4CAF50;
            color: white;
            padding: 20px 0;
            margin: 0;
        }
        h2 {
            text-align: center;
            color: #4CAF50;
            margin-top: 20px;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background-color: white;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            text-align: center;
            padding: 12px;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
        }
        button:hover {
            background-color: #45a049;
        }
        .center {
            text-align: center;
            margin: 20px;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <h1>Student Ranking System</h1>

    <?php foreach ($examData as $examId => $exam): 
        $isEditable = date('Y-m-d') <= $exam['publish_date']; // Check if ranks can be updated
    ?>
        <h2><?php echo "{$exam['title']} ({$exam['term']} term) - Grade {$grade_id}"; ?></h2>
        <form method="POST" action="">
            <input type="hidden" name="exam_id" value="<?php echo $examId; ?>">
            <input type="hidden" name="term" value="<?php echo $exam['term']; ?>">
            <table>
                <tr>
                    <th>Student Name</th>
                    <?php
                    $subjects = array_keys(current($exam['students'])['subjects']);
                    foreach ($subjects as $subject): ?>
                        <th><?php echo htmlspecialchars($subject); ?></th>
                    <?php endforeach; ?>
                    <th>Total Marks</th>
                    <th>Assign Rank</th>
                </tr>
                <?php foreach ($exam['students'] as $username => $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <?php foreach ($subjects as $subject): ?>
                            <td><?php echo $student['subjects'][$subject] ?? 'N/A'; ?></td>
                        <?php endforeach; ?>
                        <td><?php echo $student['total']; ?></td>
                        <td>
                            <input 
                                type="number" 
                                name="ranks[<?php echo $username; ?>]" 
                                value="<?php echo $student['rank'] ?? ''; ?>"
                                placeholder="Enter Rank"
                                <?php echo !$isEditable ? 'readonly' : ''; ?> >
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php if ($isEditable): ?>
                <div class="center">
                    <button type="submit" name="submit_rank">Save Ranks for <?php echo htmlspecialchars($exam['title']); ?></button>
                </div>
            <?php else: ?>
                <p class="error">Ranks cannot be updated after the publish date (<?php echo $exam['publish_date']; ?>).</p>
            <?php endif; ?>
        </form>
    <?php endforeach; ?>
</body>
</html>

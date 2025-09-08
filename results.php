<?php
include("db_connection.php");
session_start();

// Verify admin session
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Fetch all grades
$gradeQuery = "SELECT id, grade_name FROM grades";
$gradeStmt = $pdo->query($gradeQuery);
$grades = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unique terms
$termQuery = "SELECT DISTINCT term FROM exams ORDER BY term";
$termStmt = $pdo->query($termQuery);
$terms = $termStmt->fetchAll(PDO::FETCH_COLUMN);

// Initialize variables
$selectedGrade = $_POST['grade'] ?? null;
$selectedTerm = $_POST['term'] ?? null;
$examData = [];
$isEditable = false;

// Fetch exam data if grade and term are selected
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedGrade = $_POST['grade'] ?? $selectedGrade;
    $selectedTerm = $_POST['term'] ?? $selectedTerm;

    if (isset($_POST['edit'])) {
        $isEditable = true; // Enable edit mode
    } elseif (isset($_POST['save_changes'])) {
        // Save changes to the database
        $updatedData = $_POST['data'];

        foreach ($updatedData as $examId => $students) {
            foreach ($students as $username => $studentData) {
                // Update marks for each subject
                if (isset($studentData['subjects'])) {
                    foreach ($studentData['subjects'] as $subjectId => $marks) {
                        $marks = is_numeric($marks) ? $marks : null; // Ensure valid marks
                        $updateMarksQuery = "
                            INSERT INTO student_marks (exam_id, student_username, subject_id, marks)
                            VALUES (?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE marks = VALUES(marks)
                        ";
                        $stmt = $pdo->prepare($updateMarksQuery);
                        $stmt->execute([$examId, $username, $subjectId, $marks]);
                    }
                }

                // Update rank
                if (isset($studentData['rank']) && is_numeric($studentData['rank'])) {
                    // Query to check if the record exists
                    $checkRankQuery = "
                        SELECT COUNT(*) FROM ranks 
                        WHERE username = ? AND grade_id = ? AND exam_id = ? AND term = ?
                    ";
                    $checkStmt = $pdo->prepare($checkRankQuery);
                    $checkStmt->execute([$username, $selectedGrade, $examId, $selectedTerm]);
                    $exists = $checkStmt->fetchColumn() > 0;
                
                    if ($exists) {
                        // Update the existing rank
                        $updateRankQuery = "
                            UPDATE ranks 
                            SET rank = ? 
                            WHERE username = ? AND grade_id = ? AND exam_id = ? AND term = ?
                        ";
                        $updateStmt = $pdo->prepare($updateRankQuery);
                        $updateStmt->execute([$studentData['rank'], $username, $selectedGrade, $examId, $selectedTerm]);
                    } else {
                        // Insert a new rank
                        $insertRankQuery = "
                            INSERT INTO ranks (username, grade_id, exam_id, term, rank) 
                            VALUES (?, ?, ?, ?, ?)
                        ";
                        $insertStmt = $pdo->prepare($insertRankQuery);
                        $insertStmt->execute([$username, $selectedGrade, $examId, $selectedTerm, $studentData['rank']]);
                    }
                }
                
            }
        }

        echo "<script>alert('Changes saved successfully!');</script>";
    }

    // Fetch results
    $examQuery = "
        SELECT e.id AS exam_id, e.title AS exam_title, e.term, e.grade_id, sub.subject_name, sub.id AS subject_id,
               s.username, s.name, sm.marks, r.rank
        FROM exams e
        LEFT JOIN exam_subjects es ON es.exam_id = e.id
        LEFT JOIN subjects sub ON es.subject_id = sub.id
        LEFT JOIN students s ON s.grade_id = e.grade_id
        LEFT JOIN student_marks sm ON sm.exam_id = e.id AND sm.subject_id = es.subject_id AND sm.student_username = s.username
        LEFT JOIN ranks r ON r.exam_id = e.id AND r.username = s.username
        WHERE e.grade_id = ? AND e.term = ?
        ORDER BY e.id, s.username, sub.subject_name
    ";
    $examStmt = $pdo->prepare($examQuery);
    $examStmt->execute([$selectedGrade, $selectedTerm]);
    $marksData = $examStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($marksData) {
        foreach ($marksData as $row) {
            $examId = $row['exam_id'];
            $username = $row['username'];
            $subject = $row['subject_name'];
            $subjectId = $row['subject_id'];

            if (!isset($examData[$examId])) {
                $examData[$examId] = [
                    'title' => $row['exam_title'],
                    'term' => $row['term'],
                    'students' => [],
                ];
            }

            if ($username) {
                if (!isset($examData[$examId]['students'][$username])) {
                    $examData[$examId]['students'][$username] = [
                        'name' => $row['name'],
                        'subjects' => [],
                        'total' => 0,
                        'rank' => $row['rank'] ?? 'N/A',
                    ];
                }

                $marks = $row['marks'];
                $examData[$examId]['students'][$username]['subjects'][$subject] = [
                    'marks' => $marks !== null ? $marks : 'N/A',
                    'subjectId' => $subjectId,
                ];
                $examData[$examId]['students'][$username]['total'] += $marks !== null ? $marks : 0;
            }
        }
    } else {
        echo "<script>alert('No results found for the selected grade and term.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Result View</title>
    <style>
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            text-align: center;
            padding: 10px;
        }
        input[type="text"] {
            width: 50px;
        }
    </style>
</head>
<body>
    <h1 style="text-align: center;">Admin - View and Edit Results</h1>
    <form method="POST" action="" style="text-align: center; margin-bottom: 20px;">
        <label for="grade">Select Grade:</label>
        <select name="grade" id="grade" required>
            <option value="">-- Select Grade --</option>
            <?php foreach ($grades as $grade): ?>
                <option value="<?php echo $grade['id']; ?>" <?php echo ($selectedGrade == $grade['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($grade['grade_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="term">Select Term:</label>
        <select name="term" id="term" required>
            <option value="">-- Select Term --</option>
            <?php foreach ($terms as $term): ?>
                <option value="<?php echo $term; ?>" <?php echo ($selectedTerm == $term) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($term); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">View Results</button>
    </form>

    <?php if (!empty($examData)): ?>
        <form method="POST" action="">
            <input type="hidden" name="grade" value="<?php echo htmlspecialchars($selectedGrade); ?>">
            <input type="hidden" name="term" value="<?php echo htmlspecialchars($selectedTerm); ?>">

            <?php foreach ($examData as $examId => $exam): ?>
                <h2 style="text-align: center;"><?php echo "{$exam['title']} (Term {$exam['term']})"; ?></h2>
                <table>
                    <tr>
                        <th>Student Name</th>
                        <?php $subjects = array_keys(current($exam['students'])['subjects']); ?>
                        <?php foreach ($subjects as $subject): ?>
                            <th><?php echo htmlspecialchars($subject); ?></th>
                        <?php endforeach; ?>
                        <th>Total Marks</th>
                        <th>Rank</th>
                    </tr>
                    <?php foreach ($exam['students'] as $username => $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <?php foreach ($subjects as $subject): ?>
                                <td>
                                    <?php if ($isEditable): ?>
                                        <input type="text" name="data[<?php echo $examId; ?>][<?php echo $username; ?>][subjects][<?php echo $student['subjects'][$subject]['subjectId']; ?>]" value="<?php echo $student['subjects'][$subject]['marks']; ?>">
                                    <?php else: ?>
                                        <?php echo $student['subjects'][$subject]['marks']; ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td><?php echo $student['total']; ?></td>
                            <td>
                                <?php if ($isEditable): ?>
                                    <input type="text" name="data[<?php echo $examId; ?>][<?php echo $username; ?>][rank]" value="<?php echo $student['rank']; ?>">
                                <?php else: ?>
                                    <?php echo $student['rank']; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>

            <?php if ($isEditable): ?>
                <button type="submit" name="save_changes">Save Changes</button>
            <?php else: ?>
                <button type="submit" name="edit">Edit</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>
</body>
</html>

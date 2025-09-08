<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

include ("db_connection.php");
// Get the teacher's assigned class ID
$assigned_class_id = $_SESSION['assigned_class_id'];

// Fetch exams for this teacher's class, excluding those that are more than 10 days past their end date
$currentDate = date('Y-m-d');
$exam_stmt = $pdo->prepare("
    SELECT * FROM Exams 
    WHERE class_id = :class_id AND end_date >= DATE_SUB(CURDATE(), INTERVAL 10 DAY)
");
$exam_stmt->execute(['class_id' => $assigned_class_id]);
$exams = $exam_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if an exam is selected
$selected_exam_id = isset($_GET['exam_id']) ? $_GET['exam_id'] : null;

// Initialize variables for student and exam data
$student = null;
$exam = null;

// Fetch students in the assigned class only if an exam is selected
if ($selected_exam_id) {
    $student_stmt = $pdo->prepare("SELECT * FROM Students WHERE grade COLLATE utf8mb4_unicode_ci = (SELECT grade_name FROM grades WHERE id = :class_id)");
    $student_stmt->execute(['class_id' => $assigned_class_id]);
    $students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch the first student or adjust to select a specific one based on your application logic
    if (!empty($students)) {
        $student = $students[0]; // Assuming you want to edit marks for the first student
        // Fetch the exam details
        $exam_stmt = $pdo->prepare("SELECT * FROM Exams WHERE id = :exam_id");
        $exam_stmt->execute(['exam_id' => $selected_exam_id]);
        $exam = $exam_stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Fetch subjects for the selected exam if the exam is found
$subjects = [];
if ($exam) {
    $exam_subject_stmt = $pdo->prepare("SELECT subject_ids FROM Exams WHERE id = :exam_id");
    $exam_subject_stmt->execute(['exam_id' => $selected_exam_id]);
    $exam_subject = $exam_subject_stmt->fetch(PDO::FETCH_ASSOC);
    $subject_ids = explode(',', $exam_subject['subject_ids']);
    
    foreach ($subject_ids as $subject_id) {
        $subject_stmt = $pdo->prepare("SELECT * FROM Subjects WHERE id = :subject_id");
        $subject_stmt->execute(['subject_id' => $subject_id]);
        $subjects[] = $subject_stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Fetch existing marks for the selected student and exam
$existing_marks = [];
if ($student) {
    foreach ($subjects as $subject) {
        $subject_id = $subject['id'];
        $existing_marks_stmt = $pdo->prepare("SELECT marks FROM Marks WHERE student_id = :student_id AND exam_id = :exam_id AND subject_id = :subject_id");
        $existing_marks_stmt->execute([
            'student_id' => $student['id'],
            'exam_id' => $selected_exam_id,
            'subject_id' => $subject_id
        ]);
        $existing_mark = $existing_marks_stmt->fetch(PDO::FETCH_ASSOC);
        $existing_marks[$subject_id] = $existing_mark ? $existing_mark['marks'] : null;
    }

    // Fetch existing rank for the student
    $rank_stmt = $pdo->prepare("SELECT rank FROM Ranks WHERE student_id = :student_id AND exam_id = :exam_id");
    $rank_stmt->execute([
        'student_id' => $student['id'],
        'exam_id' => $selected_exam_id
    ]);
    $rank_data = $rank_stmt->fetch(PDO::FETCH_ASSOC);
    $rank = $rank_data ? $rank_data['rank'] : ''; // Initialize rank to an empty string if not found

    // Handle marks entry and rank assignment
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        foreach ($subjects as $subject) {
            $subject_id = $subject['id'];
            $marks = $_POST["marks_$subject_id"];

            // Check if marks already exist for this student, selected exam, and subject
            $existing_marks_stmt = $pdo->prepare("SELECT * FROM Marks WHERE student_id = :student_id AND exam_id = :exam_id AND subject_id = :subject_id");
            $existing_marks_stmt->execute([
                'student_id' => $student['id'],
                'exam_id' => $selected_exam_id,
                'subject_id' => $subject_id
            ]);
            
            if ($existing_marks_stmt->rowCount() > 0) {
                // Update existing marks
                $marks_stmt = $pdo->prepare("UPDATE Marks SET marks = :marks WHERE student_id = :student_id AND exam_id = :exam_id AND subject_id = :subject_id");
                $marks_stmt->execute([
                    'marks' => $marks,
                    'student_id' => $student['id'],
                    'exam_id' => $selected_exam_id,
                    'subject_id' => $subject_id
                ]);
            } else {
                // Insert new marks
                $marks_stmt = $pdo->prepare("INSERT INTO Marks (student_id, exam_id, subject_id, teacher_id, grade_id, marks) VALUES (:student_id, :exam_id, :subject_id, :teacher_id, :grade_id, :marks)");
                $marks_stmt->execute([
                    'student_id' => $student['id'],
                    'exam_id' => $selected_exam_id,
                    'subject_id' => $subject_id,
                    'teacher_id' => $_SESSION['user_id'],
                    'grade_id' => $assigned_class_id,
                    'marks' => $marks
                ]);
            }
        }

        // Insert or update rank
        $rank = $_POST['rank'];
        $rank_stmt = $pdo->prepare("SELECT * FROM Ranks WHERE student_id = :student_id AND exam_id = :exam_id");
        $rank_stmt->execute([
            'student_id' => $student['id'],
            'exam_id' => $selected_exam_id
        ]);
        $rank_data = $rank_stmt->fetch(PDO::FETCH_ASSOC);

        if ($rank_data) {
            // Update existing rank
            $update_rank_stmt = $pdo->prepare("UPDATE Ranks SET rank = :rank WHERE student_id = :student_id AND exam_id = :exam_id");
            $update_rank_stmt->execute([
                'rank' => $rank,
                'student_id' => $student['id'],
                'exam_id' => $selected_exam_id
            ]);
        } else {
            // Insert new rank
            $insert_rank_stmt = $pdo->prepare("INSERT INTO Ranks (student_id, exam_id, grade_id, rank) VALUES (:student_id, :exam_id, :grade_id, :rank)");
            $insert_rank_stmt->execute([
                'student_id' => $student['id'],
                'exam_id' => $selected_exam_id,
                'grade_id' => $assigned_class_id,
                'rank' => $rank
            ]);
        }

        echo "<script>
            alert('Marks Updated Successfully');
            window.location.href = 'select_student.php';
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Marks</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        input[type="number"], input[type="text"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .success-message {
            color: green;
            text-align: center;
        }
    </style>
</head>
<body>
    
    <div class="container">
        <h2>Enter Marks for <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($exam['exam_name']) ?>)</h2>
        <form method="POST">
            <?php foreach ($subjects as $subject): ?>
                <label for="marks_<?= $subject['id'] ?>"><?= htmlspecialchars($subject['subject_name']) ?>:</label>
                <input type="number" name="marks_<?= $subject['id'] ?>" id="marks_<?= $subject['id'] ?>" value="<?= isset($existing_marks[$subject['id']]) ? htmlspecialchars($existing_marks[$subject['id']]) : '' ?>" required>
            <?php endforeach; ?>
            <label for="rank">Rank:</label>
            <input type="text" name="rank" id="rank" value="<?= htmlspecialchars($rank) ?>" required>
            <button type="submit">Submit Marks</button>
        </form>
    </div>
</body>
</html>

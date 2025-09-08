<?php
session_start();

// Check if the user is a logged-in teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php");
    exit();
}

include ('db_connection.php');
// Get the teacher's username and assigned class ID
$teacher_username = $_SESSION['user_id'];
$assigned_class_id = $_SESSION['assigned_class_id'];

// Get the exam_id from the URL
if (!isset($_GET['exam_id'])) {
    die("Exam ID not specified.");
}
$exam_id = $_GET['exam_id'];

// Fetch the exam for this teacher's class based on exam_id
$exam_stmt = $pdo->prepare("SELECT * FROM Exams WHERE id = :exam_id AND teacher_username = :teacher_username AND class_id = :class_id");
$exam_stmt->execute([
    'exam_id' => $exam_id,
    'teacher_username' => $teacher_username,
    'class_id' => $assigned_class_id
]);
$exam = $exam_stmt->fetch(PDO::FETCH_ASSOC);

// If no exam found, show a message
if (!$exam) {
    die("No exam found for your class.");
}

// Check if the current date is past the exam end date
$current_date = date("Y-m-d");
if ($current_date > $exam['end_date']) {
    // Delete timetable entries for this exam
    $delete_stmt = $pdo->prepare("DELETE FROM ExamTimetable WHERE exam_id = :exam_id AND class_id = :class_id");
    $delete_stmt->execute([
        'exam_id' => $exam_id,
        'class_id' => $assigned_class_id
    ]);

    echo "<script>
    alert('The exam timetable has been deleted as the exam has already ended.');
    window.location.href = 'Teacher_Dashboard.php';
    </script>";
    exit();
}

// Get the subjects for this exam
$subject_ids = explode(',', $exam['subject_ids']);
$subjects = [];
foreach ($subject_ids as $subject_id) {
    $subject_stmt = $pdo->prepare("SELECT * FROM Subjects WHERE id = :subject_id");
    $subject_stmt->execute(['subject_id' => $subject_id]);
    $subjects[] = $subject_stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch existing timetable entries for this specific exam
$timetable_stmt = $pdo->prepare("SELECT * FROM ExamTimetable WHERE exam_id = :exam_id AND class_id = :class_id");
$timetable_stmt->execute([
    'exam_id' => $exam_id,
    'class_id' => $assigned_class_id
]);
$timetables = $timetable_stmt->fetchAll(PDO::FETCH_ASSOC);

// Create a mapping of subject ID to existing timetable entry
$timetable_map = [];
foreach ($timetables as $entry) {
    $timetable_map[$entry['subject_id']] = $entry;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($subjects as $subject) {
        $subject_id = $subject['id'];
        $exam_date = $_POST["exam_date_$subject_id"];
        $start_time = $_POST["start_time_$subject_id"];
        $end_time = $_POST["end_time_$subject_id"];

        // Check if an entry already exists for this subject and exam
        if (isset($timetable_map[$subject_id])) {
            // Update existing timetable entry
            $timetable_stmt = $pdo->prepare("UPDATE ExamTimetable SET exam_date = :exam_date, start_time = :start_time, end_time = :end_time
                                             WHERE exam_id = :exam_id AND class_id = :class_id AND subject_id = :subject_id");
            $timetable_stmt->execute([
                'exam_date' => $exam_date,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'exam_id' => $exam_id,
                'class_id' => $assigned_class_id,
                'subject_id' => $subject_id
            ]);
        } else {
            // Insert new timetable entry
            $timetable_stmt = $pdo->prepare("INSERT INTO ExamTimetable (exam_id, class_id, subject_id, exam_date, start_time, end_time)
                                             VALUES (:exam_id, :class_id, :subject_id, :exam_date, :start_time, :end_time)");
            $timetable_stmt->execute([
                'exam_id' => $exam_id,
                'class_id' => $assigned_class_id,
                'subject_id' => $subject_id,
                'exam_date' => $exam_date,
                'start_time' => $start_time,
                'end_time' => $end_time
            ]);
        }
    }

    echo "<script>
    alert('Exam Timetable created successfully for your class!');
    window.location.href = 'Teacher_Dashboard.php';
    </script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam Timetable</title>
    <style>
        /* Base styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f8;
            margin: 0;
            padding: 20px;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Container */
        .container {
            width: 100%;
            max-width: 800px;
            background-color: #fff;
            padding: 2em;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Header styling */
        h2 {
            color: #2c3e50;
            font-size: 1.6em;
            margin-bottom: 1.5em;
            text-align: center;
            border-bottom: 2px solid #3498db;
            display: inline-block;
            padding-bottom: 0.3em;
        }

        /* Form styling */
        form {
            margin-top: 1em;
        }

        /* Subject block styling */
        .subject-block {
            margin-bottom: 1.5em;
            padding: 1em;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        h3 {
            font-size: 1.2em;
            color: #34495e;
            margin-bottom: 0.5em;
            border-left: 4px solid #3498db;
            padding-left: 10px;
        }

        /* Label and input styling */
        label {
            font-weight: bold;
            margin-right: 10px;
            color: #555;
        }

        input[type="date"], input[type="time"] {
            padding: 0.4em;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9em;
            margin-bottom: 0.5em;
            width: calc(33% - 12px);
            margin-right: 10px;
            box-sizing: border-box;
        }

        /* Button styling */
        .submit-button {
            display: block;
            width: 100%;
            padding: 0.7em;
            font-size: 1em;
            font-weight: bold;
            color: #fff;
            background-color: #3498db;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<div class="container">
    <?php include('navbar.php'); ?>
    
    <h2>Create/Update Exam Timetable for Your Class</h2>

    <form method="POST" action="">
        <?php foreach ($subjects as $subject): ?>
            <div class="subject-block">
                <h3><?= htmlspecialchars($subject['subject_name']) ?></h3>
                
                <label for="exam_date_<?= $subject['id'] ?>">Exam Date:</label>
                <input type="date" id="exam_date_<?= $subject['id'] ?>" name="exam_date_<?= $subject['id'] ?>" 
                       value="<?= isset($timetable_map[$subject['id']]) ? htmlspecialchars($timetable_map[$subject['id']]['exam_date']) : '' ?>" required>

                <label for="start_time_<?= $subject['id'] ?>">Start Time:</label>
                <input type="time" id="start_time_<?= $subject['id'] ?>" name="start_time_<?= $subject['id'] ?>" 
                       value="<?= isset($timetable_map[$subject['id']]) ? htmlspecialchars($timetable_map[$subject['id']]['start_time']) : '' ?>" required>

                <label for="end_time_<?= $subject['id'] ?>">End Time:</label>
                <input type="time" id="end_time_<?= $subject['id'] ?>" name="end_time_<?= $subject['id'] ?>" 
                       value="<?= isset($timetable_map[$subject['id']]) ? htmlspecialchars($timetable_map[$subject['id']]['end_time']) : '' ?>" required>
            </div>
        <?php endforeach; ?>
        
        <button type="submit" class="submit-button">Save Timetable</button>
    </form>
</div>

</body>
</html>

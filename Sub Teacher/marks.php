<?php
include("db_connection.php");
session_start();


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'NoClass_Teacher') {
    header("Location: login.php");
    exit;
}


$username = htmlspecialchars($_SESSION['user']['username']);

// Fetch teacher's assigned subject and valid teaching_classes (grades with exams for their subject)
$sql = "SELECT DISTINCT t.subject_id, 
                       t.teaching_classes
        FROM noclass_teacher t
        JOIN exams e ON t.teaching_classes LIKE CONCAT('%\"', e.grade_id, '\"%')
        JOIN exam_subjects es ON e.id = es.exam_id
        WHERE t.username = ? AND es.subject_id = t.subject_id";

$stmt = $pdo->prepare($sql);
$stmt->execute([$username]);
$teacher = $stmt->fetch();

if (!$teacher) {
    die("No grades or subject found for the teacher.");
}

$subject_id = $teacher['subject_id'];
$teaching_classes = json_decode($teacher['teaching_classes'], true); // Decode JSON array of grades

if (!$teaching_classes) {
    die("No valid teaching classes found for this teacher.");
}

// Fetch subject name based on subject_id
$sql_subject = "SELECT subject_name FROM subjects WHERE id = ?";
$stmt = $pdo->prepare($sql_subject);
$stmt->execute([$subject_id]);
$subject = $stmt->fetch();

if (!$subject) {
    die("Subject not found.");
}

$subject_name = $subject['subject_name'];

// Fetch exams grouped by grade and include exam_date
$sql = "SELECT e.id, e.title, e.term, e.grade_id, e.start_date, e.end_date, e.publish_date, es.exam_date
        FROM exams e
        JOIN exam_subjects es ON e.id = es.exam_id
        WHERE e.grade_id IN (" . implode(',', $teaching_classes) . ") 
        AND es.subject_id = ?
        AND e.end_date >= CURDATE()
        ORDER BY e.grade_id, e.term";
$stmt = $pdo->prepare($sql);
$stmt->execute([$subject_id]);
$exams = $stmt->fetchAll();

// Organize exams by grade
$exams_by_grade = [];
foreach ($exams as $exam) {
    $exams_by_grade[$exam['grade_id']][] = $exam;
}

// Fetch students by grade (using username)
$students_by_grade = [];
foreach ($teaching_classes as $grade_id) {
    $sql_students = "SELECT s.username, s.name 
                     FROM students s
                     WHERE s.grade_id = ?";
    $stmt = $pdo->prepare($sql_students);
    $stmt->execute([$grade_id]);
    $students_by_grade[$grade_id] = $stmt->fetchAll();
}

// Fetch existing marks for students by grade, exam, and subject
$existing_marks = [];
$sql_marks = "SELECT exam_id, student_username, marks FROM student_marks WHERE subject_id = ?";
$stmt = $pdo->prepare($sql_marks);
$stmt->execute([$subject_id]);
$marks_data = $stmt->fetchAll();
foreach ($marks_data as $data) {
    $existing_marks[$data['exam_id']][$data['student_username']] = $data['marks'];
}

// Handle form submission for marks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($exams_by_grade as $grade_id => $exams) {
        foreach ($exams as $exam) {
            $exam_id = $exam['id'];
            $publish_date = $exam['publish_date'];

            if (strtotime($publish_date) > time()) { // Allow marks only if publish date hasn't passed
                foreach ($students_by_grade[$grade_id] as $student) {
                    $student_username = $student['username'];
                    $marks = $_POST["marks"][$grade_id][$exam_id][$student_username] ?? null;

                    if ($marks !== null) {
                        // Insert/update marks for the student
                        $sql_marks = "INSERT INTO student_marks (exam_id, student_username, subject_id, marks)
                                      VALUES (?, ?, ?, ?)
                                      ON DUPLICATE KEY UPDATE marks = ?";
                        $stmt = $pdo->prepare($sql_marks);
                        $stmt->execute([$exam_id, $student_username, $subject_id, $marks, $marks]);
                    }
                }
            }
        }
    }
    // Display JavaScript alert and redirect
    echo "<script>
            alert('Marks have been saved successfully!');
            window.location.href = 'marks.php';
          </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marking System - <?php echo $subject_name; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .marks_container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .exam-section {
            margin-bottom: 30px;
        }
        .exam-section h3 {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            border-radius: 4px;
        }
        .exam-section h4 {
            color: #555;
            margin: 10px 0;
        }
        .warning, .error {
            color: #ff0000;
            font-weight: bold;
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
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        input[type="number"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button[type="submit"] {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        button[type="submit"]:hover {
            background-color: #218838;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert.success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="marks_container">
        <?php include('navbar.php'); ?>

        <h2>Markings for <?php echo $subject_name; ?></h2>

        <?php if (isset($message)): ?>
            <div class="alert <?php echo $message['type']; ?>">
                <?php echo $message['text']; ?>
            </div>
        <?php endif; ?>

        <?php foreach ($exams_by_grade as $grade_id => $exams): ?>
            <?php foreach ($exams as $exam): ?>
                <div class="exam-section">
                    <h3><?php echo $exam['title']; ?> (Term <?php echo $exam['term']; ?>) - Grade <?php echo $grade_id; ?></h3>
                    <h4>Exam Date: <?php echo $exam['exam_date']; ?> | Exam Period: <?php echo $exam['start_date']; ?> to <?php echo $exam['end_date']; ?></h4>

                    <!-- Check if marks entry is allowed -->
                    <?php 
                    $exam_date = strtotime($exam['exam_date']);
                    $current_date = strtotime(date("Y-m-d"));
                    if ($current_date < $exam_date): // Exam date hasn't reached yet
                    ?>
                        <p class="warning">The exam hasn't started yet. Marks entry will be allowed starting from the exam date: <?php echo date("Y-m-d", $exam_date); ?>.</p>
                    <?php elseif ($current_date >= $exam_date && strtotime($exam['publish_date']) > $current_date): // After exam date and before publish date
                    ?>
                        <form method="POST" onsubmit="return validateMarksForm(this);">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Marks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students_by_grade[$grade_id] as $student): ?>
                                        <tr>
                                            <td><?php echo $student['name']; ?></td>
                                            <td>
                                                <input type="number" 
                                                    name="marks[<?php echo $grade_id; ?>][<?php echo $exam['id']; ?>][<?php echo $student['username']; ?>]" 
                                                    min="0" max="100" 
                                                    value="<?php echo $existing_marks[$exam['id']][$student['username']] ?? ''; ?>"
                                                    required>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <button type="submit">Save Marks</button>
                        </form>
                    <?php else: // Exam has passed and publish date is also passed ?>
                        <p class="error">Marks cannot be updated as the publish date has passed.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <script>
        // JavaScript to validate the marks form (ensuring values are within the valid range)
        function validateMarksForm(form) {
            const inputs = form.querySelectorAll('input[type="number"]');
            for (const input of inputs) {
                const value = input.value;
                if (value < 0 || value > 100) {
                    alert("Marks must be between 0 and 100.");
                    return false;
                }
            }
            return true;
        }
    </script>
</body>
</html>

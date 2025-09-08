<?php
session_start();

// Check if the user is a logged-in teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    header("Location: ../login.php");
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'sms';
$dbUsername = 'root';
$dbPassword = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get the teacher's ID and assigned class ID
$teacher_id = $_SESSION['user_id'];
$assigned_class_id = $_SESSION['assigned_class_id'];

// Fetch subjects from the database
$subject_stmt = $pdo->prepare("SELECT * FROM Subjects");
$subject_stmt->execute();
$subjects = $subject_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_type = $_POST['exam_type'];
    $exam_title = $_POST['exam_title'];
    $selected_subjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Convert selected subject IDs into a comma-separated string
    $subject_ids = implode(',', $selected_subjects);

    // Insert exam details into the Exams table
    $exam_stmt = $pdo->prepare("INSERT INTO Exams (teacher_username, class_id, exam_type, exam_title, subject_ids, start_date, end_date)
                            VALUES (:teacher_username, :class_id, :exam_type, :exam_title, :subject_ids, :start_date, :end_date)");

    $exam_stmt->execute([
        'teacher_username' => $teacher_id,  // Make sure $teacher_id actually holds the teacher's username
        'class_id' => $assigned_class_id,
        'exam_type' => $exam_type,
        'exam_title' => $exam_title,
        'subject_ids' => $subject_ids,
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);

    echo "<script>
    alert('Exam created successfully for your class!');
    window.location.href = 'Teacher_Dashboard.php';
</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Exam</title>
    <style>
        /* Base styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        /* Container styling */
        .container {
            width: 100%;
            max-width: 600px;
            background-color: #fff;
            padding: 2em;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-top: -100px;
        }

        /* Header */
        h2 {
            color: #2c3e50;
            font-size: 1.5em;
            margin-bottom: 1.5em;
            border-bottom: 2px solid #3498db;
            display: inline-block;
            padding-bottom: 0.3em;
        }

        /* Form styling */
        form {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        label {
            font-weight: bold;
            margin-top: 1em;
            color: #555;
        }

        input[type="text"],
        input[type="date"],
        .subject-list {
            width: 100%;
            padding: 0.5em;
            margin-top: 0.3em;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }

        .subject-list {
            display: flex;
            flex-wrap: wrap;
        }

        .subject-item {
            flex-basis: 48%;
            margin-bottom: 0.5em;
        }

        input[type="checkbox"] {
            margin-right: 0.5em;
        }

        /* Button styling */
        button {
            width: 100%;
            padding: 0.75em;
            margin-top: 1.5em;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        /* Responsive styling */
        @media (max-width: 600px) {
            .container {
                padding: 1.5em;
            }
            .subject-item {
                flex-basis: 100%;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container">
    <h2>Create Exam for Your Class</h2>

    <form method="POST" action="">
        <label for="exam_type">Exam Type:</label>
        <input type="text" id="exam_type" name="exam_type" required>

        <label for="exam_title">Exam Title:</label>
        <input type="text" id="exam_title" name="exam_title" required>

        <label for="subjects">Select Subjects:</label>
        <div class="subject-list">
            <?php foreach ($subjects as $subject): ?>
                <div class="subject-item">
                    <input type="checkbox" name="subjects[]" value="<?= htmlspecialchars($subject['id']) ?>">
                    <?= htmlspecialchars($subject['subject_name']) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required>

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>

        <button type="submit">Create Exam</button>
    </form>
</div>

</body>
</html>

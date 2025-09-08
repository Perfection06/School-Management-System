<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connection.php';

// Fetch grades
$grades = $pdo->query("SELECT id, grade_name FROM grades")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $examTitle = $_POST['exam_title'];
    $term = $_POST['term'];
    $gradeId = $_POST['grade'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $publishDate = $_POST['publish_date'];
    $subjectDetails = $_POST['subject_details'] ?? []; // Array of subject IDs with time/date

    try {
        $pdo->beginTransaction();

        // Insert exam details
        $stmt = $pdo->prepare("INSERT INTO exams (title, term, grade_id, start_date, end_date, publish_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$examTitle, $term, $gradeId, $startDate, $endDate, $publishDate]);

        // Get the inserted exam ID
        $examId = $pdo->lastInsertId();

        // Insert subject details
        $stmt = $pdo->prepare("INSERT INTO exam_subjects (exam_id, subject_id, exam_date, exam_time) VALUES (?, ?, ?, ?)");
        foreach ($subjectDetails as $detail) {
            if (
                isset($detail['subject_id'], $detail['exam_date'], $detail['exam_time']) &&
                !empty($detail['exam_date']) &&
                !empty($detail['exam_time'])
            ) {
                $stmt->execute([$examId, $detail['subject_id'], $detail['exam_date'], $detail['exam_time']]);
            }
        }

        $pdo->commit();
        echo "<script>alert('Exam created successfully!'); window.location.href='create_exam.php';</script>";
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Failed to create exam: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Main Exam</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .exam_container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: 40px auto;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            margin-top: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            color: #555;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        input:focus, select:focus, button:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 4px rgba(0, 123, 255, 0.5);
        }

        button {
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }

        #subjects-container {
            margin-top: 20px;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .grid-item {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #f8f8f8;
        }

        .grid-item input[type="checkbox"] {
            margin-right: 5px;
        }

        .grid-item label {
            margin-top: 10px;
        }

        .grid-item input[type="date"],
        .grid-item input[type="time"] {
            width: calc(100% - 20px);
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="exam_container">
        <h1>Create Main Exam</h1>
        <form id="examForm" method="POST" action="create_exam.php"> <!-- Set method to POST -->
            <label for="exam_title">Exam Title:</label>
            <input type="text" id="exam_title" name="exam_title" placeholder="Enter exam title" required>

            <label for="term">Term:</label>
            <select id="term" name="term" required>
                <option value="">--Select Term--</option>
                <option value="1">1st Term</option>
                <option value="2">2nd Term</option>
                <option value="3">3rd Term</option>
            </select>

            <label for="grade">Grade:</label>
            <select id="grade" name="grade" required>
                <option value="">--Select Grade--</option>
                <?php foreach ($grades as $grade): ?>
                    <option value="<?= $grade['id']; ?>">
                        <?= $grade['grade_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>

            <label for="publish_date">Publish Date:</label>
            <input type="date" id="publish_date" name="publish_date" required>

            <h3>Subjects and Time</h3>
            <div id="subjects-container">
                <p>Select a grade to load subjects.</p>
            </div>

            <button type="submit">Create Exam</button>
        </form>
    </div>

    <script>
        $(document).ready(function () {
            // Fetch subjects dynamically
            $('#grade').change(function () {
                var gradeId = $(this).val();
                if (gradeId) {
                    $.ajax({
                        url: 'fetch_subjects.php',
                        method: 'GET',
                        data: { grade_id: gradeId },
                        success: function (response) {
                            try {
                                var subjects = JSON.parse(response);
                                var html = '<div class="grid-container">';
                                subjects.forEach(function (subject) {
                                    html += `
                                        <div class="grid-item">
                                            <input type="checkbox" id="subject_${subject.id}" name="subject_details[${subject.id}][subject_id]" value="${subject.id}">
                                            <label for="subject_${subject.id}">${subject.subject_name}</label>
                                            <label for="subject_date_${subject.id}">Date:</label>
                                            <input type="date" id="subject_date_${subject.id}" name="subject_details[${subject.id}][exam_date]" disabled>
                                            <label for="subject_time_${subject.id}">Time:</label>
                                            <input type="time" id="subject_time_${subject.id}" name="subject_details[${subject.id}][exam_time]" disabled>
                                        </div>
                                    `;
                                });
                                html += '</div>';
                                $('#subjects-container').html(html);
                            } catch (error) {
                                alert('Error parsing subjects: ' + error.message);
                            }
                        },
                        error: function () {
                            alert('Failed to fetch subjects. Please try again.');
                        }
                    });
                } else {
                    $('#subjects-container').html('<p>Select a grade to load subjects.</p>');
                }
            });

            // Enable/disable date and time inputs based on checkbox state
            $(document).on('change', 'input[type="checkbox"]', function () {
                const subjectId = $(this).val();
                const isChecked = $(this).is(':checked');
                $(`#subject_date_${subjectId}`).prop('disabled', !isChecked).prop('required', isChecked);
                $(`#subject_time_${subjectId}`).prop('disabled', !isChecked).prop('required', isChecked);
            });

            // Validate form submission
            $('#examForm').on('submit', function (e) {
                const checkedSubjects = $('input[type="checkbox"]:checked').length;
                if (checkedSubjects === 0) {
                    alert('Please select at least one subject and provide its date and time.');
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connection.php';

// Fetch all exams that have not ended
$examsQuery = "
    SELECT e.id, e.title, e.term, e.start_date, e.end_date, g.grade_name
    FROM exams e
    INNER JOIN grades g ON e.grade_id = g.id
    WHERE e.end_date >= CURRENT_DATE()  -- Only show exams with end dates after today
    ORDER BY e.start_date DESC
";
$exams = $pdo->query($examsQuery)->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_POST['delete_exam'])) {
    $examId = $_POST['exam_id'];

    try {
        $pdo->beginTransaction();

        // Delete related exam subjects
        $pdo->prepare("DELETE FROM exam_subjects WHERE exam_id = ?")->execute([$examId]);

        // Delete the exam
        $pdo->prepare("DELETE FROM exams WHERE id = ?")->execute([$examId]);

        $pdo->commit();
        echo "<script>alert('Exam deleted successfully!'); window.location.href='manage_exam.php';</script>";
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Failed to delete exam: " . $e->getMessage() . "');</script>";
    }
}

// Handle edit request
if (isset($_POST['update_exam'])) {
    $examId = $_POST['exam_id'];
    $subjects = $_POST['subjects'];

    // Fetch the start date of the exam
    $stmt = $pdo->prepare("SELECT start_date FROM exams WHERE id = ?");
    $stmt->execute([$examId]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exam || strtotime($exam['start_date']) <= time()) {
        echo "<script>alert('Editing exams with past or ongoing start dates is not allowed.'); window.location.href='manage_exam.php';</script>";
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Update subject details
        foreach ($subjects as $subject) {
            if (
                isset($subject['subject_id'], $subject['exam_date'], $subject['exam_time']) &&
                !empty($subject['exam_date']) &&
                !empty($subject['exam_time'])
            ) {
                $stmt = $pdo->prepare("
                    UPDATE exam_subjects 
                    SET exam_date = ?, exam_time = ?
                    WHERE exam_id = ? AND subject_id = ?
                ");
                $stmt->execute([
                    $subject['exam_date'],
                    $subject['exam_time'],
                    $examId,
                    $subject['subject_id']
                ]);
            }
        }

        $pdo->commit();
        echo "<script>alert('Exam updated successfully!'); window.location.href='manage_exam.php';</script>";
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<script>alert('Failed to update exam: " . $e->getMessage() . "');</script>";
    }
}

// Fetch exam subjects for a specific exam (used in the front end dynamically)
if (isset($_GET['exam_id'])) {
    $examId = $_GET['exam_id'];
    $subjectsQuery = "
        SELECT es.subject_id, s.subject_name, es.exam_date, es.exam_time
        FROM exam_subjects es
        INNER JOIN subjects s ON es.subject_id = s.id
        WHERE es.exam_id = ?
    ";
    $stmt = $pdo->prepare($subjectsQuery);
    $stmt->execute([$examId]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exams</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            text-align: center;
            padding: 10px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }

        button.delete {
            background-color: #dc3545;
        }

        button:hover {
            opacity: 0.9;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 80%;
        }

        .modal.active {
            display: block;
        }

        .modal h2 {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Exams</h1>
    <table>
        <thead>
        <tr>
            <th>Title</th>
            <th>Term</th>
            <th>Grade</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($exams as $exam): ?>
            <tr>
                <td><?= htmlspecialchars($exam['title']); ?></td>
                <td><?= htmlspecialchars($exam['term']); ?></td>
                <td><?= htmlspecialchars($exam['grade_name']); ?></td>
                <td><?= htmlspecialchars($exam['start_date']); ?></td>
                <td><?= htmlspecialchars($exam['end_date']); ?></td>
                <td>
                    <?php
                    $isEditable = strtotime($exam['start_date']) > time();
                    ?>
                    <?php if ($isEditable): ?>
                        <button class="edit-btn" data-exam-id="<?= $exam['id']; ?>">Edit</button>
                    <?php else: ?>
                        <button disabled style="cursor: not-allowed; opacity: 0.6;">Edit (Locked)</button>
                    <?php endif; ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="exam_id" value="<?= $exam['id']; ?>">
                        <button type="submit" name="delete_exam" class="delete">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Editing Exam -->
<div id="editModal" class="modal">
    <h2>Edit Exam</h2>
    <form id="editForm" method="POST">
        <input type="hidden" name="exam_id" id="exam_id">
        <div id="subjects-container"></div>
        <button type="submit" name="update_exam">Save Changes</button>
        <button type="button" id="closeModal">Close</button>
    </form>
</div>

<script>
    $(document).ready(function () {
        $('.edit-btn').click(function () {
            const examId = $(this).data('exam-id');
            $('#exam_id').val(examId);

            $.ajax({
                url: 'manage_exam.php',
                method: 'GET',
                data: { exam_id: examId },
                success: function (response) {
                    const subjects = JSON.parse(response);
                    let html = '';
                    subjects.forEach(subject => {
                        html += `
                            <div>
                                <label>${subject.subject_name}</label>
                                <input type="hidden" name="subjects[${subject.subject_id}][subject_id]" value="${subject.subject_id}">
                                <input type="date" name="subjects[${subject.subject_id}][exam_date]" value="${subject.exam_date}">
                                <input type="time" name="subjects[${subject.subject_id}][exam_time]" value="${subject.exam_time}">
                            </div>
                        `;
                    });
                    $('#subjects-container').html(html);
                    $('#editModal').addClass('active');
                }
            });
        });

        $('#closeModal').click(function () {
            $('#editModal').removeClass('active');
        });
    });
</script>
</body>
</html>

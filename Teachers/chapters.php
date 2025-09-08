<?php
session_start();

// Check if user is logged in and is a Teacher
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

include("db_connection.php");

// Get the logged-in teacher's username
$username = $_SESSION['user']['username'];

// Fetch the teacher's subject_id and teaching_classes (grade IDs)
$queryTeacher = "SELECT subject_id, teaching_classes FROM teacher WHERE username = :username";
$stmtTeacher = $pdo->prepare($queryTeacher);
$stmtTeacher->execute([':username' => $username]);
$teacher = $stmtTeacher->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    die("Teacher not found.");
}

// Decode the teaching_classes JSON to get the assigned grade IDs
$teaching_classes = json_decode($teacher['teaching_classes'], true);

// Check if there are any assigned grades
if (empty($teaching_classes)) {
    die("No assigned grades for this teacher.");
}

// Get the teacher's subject_id
$subject_id = $teacher['subject_id'];

// Dynamically create placeholders for the IN clause based on the number of teaching_classes
$placeholders = implode(',', array_fill(0, count($teaching_classes), '?'));

// Build the query to fetch chapters based on the teacher's subject and grades
$queryChapters = "
    SELECT c.id, c.chapter_name, c.periods_allocated, c.term, c.grade_id, c.completion_status, 
           c.finished_on_time, c.extra_periods, c.reason, g.grade_name
    FROM chapters c
    JOIN grades g ON c.grade_id = g.id
    WHERE c.subject_id = ? AND c.grade_id IN ($placeholders)
    ORDER BY c.grade_id
";

// Prepare the parameters (subject_id and the grade_ids)
$params = array_merge([$subject_id], $teaching_classes); // First add subject_id, then append grade_ids

// Prepare and execute the query
$stmtChapters = $pdo->prepare($queryChapters);
$stmtChapters->execute($params);

// Fetch the chapters
$chapters = $stmtChapters->fetchAll(PDO::FETCH_ASSOC);

// Group chapters by grade_id
$groupedChapters = [];
foreach ($chapters as $chapter) {
    $groupedChapters[$chapter['grade_id']][] = $chapter;
}

function generateProgressBar($chapters) {
    $progressBar = '<div class="progress-bar">';
    foreach ($chapters as $chapter) {
        if (!$chapter['completion_status']) {
            $colorClass = 'gray'; // Not started
        } elseif ($chapter['finished_on_time']) {
            $colorClass = 'green'; // Completed on time
        } else {
            $colorClass = 'red'; // Completed late
        }
        $progressBar .= '<div class="progress-box ' . $colorClass . '"></div>';
    }
    $progressBar .= '</div>';
    return $progressBar;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chapter Progress</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #4caf50;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .progress-bar-container {
            margin: 20px 0;
            text-align: center;
        }
        .progress-bar {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin: 10px auto;
        }
        .progress-box {
            width: 25px;
            height: 25px;
            border: 1px solid #ddd;
            border-radius: 3px;
            transition: background-color 0.3s ease;
        }
        .progress-box.gray {
            background-color: #bbb;
        }
        .progress-box.green {
            background-color: #4caf50;
        }
        .progress-box.red {
            background-color: #f44336;
        }
        .progress-label {
            margin-top: 10px;
            font-weight: bold;
            color: #555;
        }
        button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #4caf50;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #45a049;
        }
        textarea, input, select {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        textarea:disabled, input:disabled, select:disabled {
            background-color: #f9f9f9;
            cursor: not-allowed;
        }
        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }
            .progress-box {
                width: 20px;
                height: 20px;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <h1>Chapter Progress</h1>

    <?php if (empty($groupedChapters)): ?>
        <p>No chapters found for the assigned grades and subject.</p>
    <?php else: ?>
        <form action="update_chapters.php" method="POST">
            <?php foreach ($groupedChapters as $grade_id => $gradeChapters): ?>
                <h2>Grade <?= htmlspecialchars($grade_id) ?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Chapter Name</th>
                            <th>Periods Allocated</th>
                            <th>Term</th>
                            <th>Completed</th>
                            <th>Finished On Time</th>
                            <th>Extra Periods</th>
                            <th>Reason for Delay</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gradeChapters as $chapter): ?>
                            <tr>
                                <td><?= htmlspecialchars($chapter['chapter_name']) ?></td>
                                <td><?= $chapter['periods_allocated'] ?></td>
                                <td><?= htmlspecialchars($chapter['term']) ?></td>
                                <td>
                                    <input type="checkbox" 
                                           name="completion_status[<?= $chapter['id'] ?>]" 
                                           <?= $chapter['completion_status'] ? 'checked' : '' ?>>
                                </td>
                                <td>
                                    <select name="finished_on_time[<?= $chapter['id'] ?>]" 
                                            class="finished-on-time" 
                                            data-chapter-id="<?= $chapter['id'] ?>">
                                        <option value="1" <?= $chapter['finished_on_time'] ? 'selected' : '' ?>>Yes</option>
                                        <option value="0" <?= !$chapter['finished_on_time'] ? 'selected' : '' ?>>No</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="extra_periods[<?= $chapter['id'] ?>]" 
                                           class="extra-periods" 
                                           data-chapter-id="<?= $chapter['id'] ?>" 
                                           value="<?= $chapter['extra_periods'] ?>" 
                                           min="0" 
                                           <?= !$chapter['finished_on_time'] ? '' : 'disabled' ?>>
                                </td>
                                <td>
                                    <textarea name="reason[<?= $chapter['id'] ?>]" 
                                              class="reason" 
                                              data-chapter-id="<?= $chapter['id'] ?>" 
                                              <?= !$chapter['finished_on_time'] ? '' : 'disabled' ?>><?= $chapter['reason'] ?></textarea>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Progress Bar -->
                <div class="progress-bar-container">
                    <?= generateProgressBar($gradeChapters) ?>
                    <div class="progress-label">
                        <?= round((count(array_filter($gradeChapters, fn($c) => $c['completion_status'])) / count($gradeChapters)) * 100) ?>% Completed
                    </div>
                </div>
            <?php endforeach; ?>
            <button type="submit">Update Chapters</button>
        </form>
    <?php endif; ?>
    
    <script>
    // Select all the dropdowns with the "finished-on-time" class
    document.querySelectorAll('.finished-on-time').forEach(function(select) {
        select.addEventListener('change', function() {
            var chapterId = this.dataset.chapterId;
            var isOnTime = this.value === "1";

            var extraPeriodsField = document.querySelector('.extra-periods[data-chapter-id="' + chapterId + '"]');
            var reasonField = document.querySelector('.reason[data-chapter-id="' + chapterId + '"]');

            if (isOnTime) {
                extraPeriodsField.value = "";
                reasonField.value = "";
                extraPeriodsField.disabled = true;
                reasonField.disabled = true;
            } else {
                extraPeriodsField.disabled = false;
                reasonField.disabled = false;
            }
        });
    });
    </script>
</body>
</html>
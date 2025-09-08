<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include("db_connection.php");

$subject_id = $_GET['subject_id'] ?? null;

// Validate input
if (!$subject_id) {
    die("Invalid Subject ID.");
}

// Fetch subject name
$querySubject = "SELECT subject_name FROM subjects WHERE id = :subject_id";
$stmtSubject = $pdo->prepare($querySubject);
$stmtSubject->execute([':subject_id' => $subject_id]);
$subject = $stmtSubject->fetch(PDO::FETCH_ASSOC);

if (!$subject) {
    die("Subject not found.");
}

// Fetch grades for the subject
$queryGrades = "
    SELECT g.id AS grade_id, g.grade_name
    FROM grades g
    WHERE EXISTS (
        SELECT 1 FROM chapters c
        WHERE c.subject_id = :subject_id AND c.grade_id = g.id
    )
";
$stmtGrades = $pdo->prepare($queryGrades);
$stmtGrades->execute([':subject_id' => $subject_id]);
$grades = $stmtGrades->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress for <?= htmlspecialchars($subject['subject_name']) ?></title>
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
            margin-bottom: 30px;
        }
        .grade-section {
            margin-bottom: 40px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .grade-title {
            font-size: 22px;
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
            text-align: center;
            margin: 20px 0;
        }
        .progress-bar {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin: 10px auto;
        }
        .progress-block {
            width: 30px;
            height: 30px;
            border-radius: 3px;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }
        .progress-block.gray {
            background-color: #bbb;
        }
        .progress-block.green {
            background-color: #4caf50;
        }
        .progress-block.red {
            background-color: #f44336;
        }
        .progress-block:hover {
            transform: scale(1.1);
            cursor: pointer;
        }
        .progress-label {
            margin-top: 10px;
            font-weight: bold;
            color: #555;
        }
        .summary {
            font-size: 18px;
            color: #555;
            text-align: center;
            margin-top: 10px;
        }
        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }
            .progress-block {
                width: 25px;
                height: 25px;
            }
        }
    </style>
</head>
<body>
    <h1>Progress for <?= htmlspecialchars($subject['subject_name']) ?></h1>

    <?php if (count($grades) === 0): ?>
        <p style="text-align: center; font-size: 18px;">No progress report available.</p>
    <?php else: ?>
        <?php foreach ($grades as $grade): ?>
            <div class="grade-section">
                <div class="grade-title"><?= htmlspecialchars($grade['grade_name']) ?></div>

                <?php
                // Fetch chapters for the current grade and subject
                $queryChapters = "
                    SELECT chapter_name, periods_allocated, term, completion_status, finished_on_time, extra_periods, reason
                    FROM chapters
                    WHERE subject_id = :subject_id AND grade_id = :grade_id
                ";
                $stmtChapters = $pdo->prepare($queryChapters);
                $stmtChapters->execute([
                    ':subject_id' => $subject_id,
                    ':grade_id' => $grade['grade_id']
                ]);
                $chapters = $stmtChapters->fetchAll(PDO::FETCH_ASSOC);

                // Calculate progress
                $totalChapters = count($chapters);
                $completedChapters = count(array_filter($chapters, fn($c) => $c['completion_status']));
                $progressPercentage = $totalChapters > 0 ? round(($completedChapters / $totalChapters) * 100) : 0;
                ?>

                <?php if ($totalChapters === 0): ?>
                    <p>No chapters available for <?= htmlspecialchars($grade['grade_name']) ?></p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Chapter Name</th>
                                <th>Periods Allocated</th>
                                <th>Term</th>
                                <th>Completion Status</th>
                                <th>Finished on Time</th>
                                <th>Extra Periods</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($chapters as $chapter): ?>
                                <tr>
                                    <td><?= htmlspecialchars($chapter['chapter_name']) ?></td>
                                    <td><?= $chapter['periods_allocated'] ?></td>
                                    <td><?= htmlspecialchars($chapter['term']) ?></td>
                                    <td><?= $chapter['completion_status'] ? 'Completed' : 'Not Completed' ?></td>
                                    <td><?= $chapter['finished_on_time'] === null ? 'N/A' : ($chapter['finished_on_time'] ? 'Yes' : 'No') ?></td>
                                    <td><?= $chapter['extra_periods'] ?></td>
                                    <td><?= htmlspecialchars($chapter['reason'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Progress Bar -->
                    <div class="progress-bar-container">
                        <div class="progress-bar">
                            <?php foreach ($chapters as $chapter): ?>
                                <div class="progress-block 
                                    <?= $chapter['completion_status'] 
                                        ? ($chapter['finished_on_time'] === 0 ? 'red' : 'green') 
                                        : 'gray' ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="progress-label"><?= $progressPercentage ?>% Completed</div>
                    </div>


                    <!-- Summary -->
                    <div class="summary">
                        <?= $progressPercentage ?>% of chapters completed for <?= htmlspecialchars($grade['grade_name']) ?>.
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

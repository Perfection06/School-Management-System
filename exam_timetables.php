<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'db_connection.php';

// Fetch grades from the database
$grades = $pdo->query("SELECT id, grade_name FROM grades")->fetchAll(PDO::FETCH_ASSOC);

// Fetch distinct years from the exams table
$years = $pdo->query("SELECT DISTINCT YEAR(start_date) as year FROM exams ORDER BY year DESC")->fetchAll(PDO::FETCH_ASSOC);

// Initialize variables
$selectedGrade = $_GET['grade'] ?? '';
$selectedYear = $_GET['year'] ?? '';
$examTimetable = [];

// Fetch the timetable grouped by exam title and term if both grade and year are selected
if ($selectedGrade && $selectedYear) {
    $stmt = $pdo->prepare("
        SELECT e.title, e.term, es.exam_date, es.exam_time, s.subject_name
        FROM exams e
        JOIN exam_subjects es ON e.id = es.exam_id
        JOIN subjects s ON es.subject_id = s.id
        WHERE e.grade_id = ? AND YEAR(e.start_date) = ?
        ORDER BY e.title, e.term, es.exam_date, es.exam_time
    ");
    $stmt->execute([$selectedGrade, $selectedYear]);
    $examTimetable = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Timetable</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1, h2 {
            text-align: center;
            color: #333;
        }

        form {
            background-color: #fff;
            padding: 20px;
            margin: 20px auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        select, button {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            max-width: 300px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        button {
            background-color: #007BFF;
            color: white;
            border: none;
            font-size: 18px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            text-align: left;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #007BFF;
            color: white;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .print-button {
            text-align: center;
            margin: 20px;
        }

        .print-button button {
            background-color: #28a745;
            border: none;
            padding: 8px 15px;
            font-size: 16px;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .print-button button:hover {
            background-color: #218838;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .print-section, .print-section * {
                visibility: visible;
            }

            .print-section {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <h1>View Exam Timetable</h1>

    <form method="get">
        <label for="grade">Select Grade:</label>
        <select id="grade" name="grade" required>
            <option value="">--Select Grade--</option>
            <?php foreach ($grades as $grade): ?>
                <option value="<?= $grade['id']; ?>" <?= $selectedGrade == $grade['id'] ? 'selected' : ''; ?>>
                    <?= $grade['grade_name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="year">Select Year:</label>
        <select id="year" name="year" required>
            <option value="">--Select Year--</option>
            <?php foreach ($years as $year): ?>
                <option value="<?= $year['year']; ?>" <?= $selectedYear == $year['year'] ? 'selected' : ''; ?>>
                    <?= $year['year']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">View Timetable</button>
    </form>

    <?php if ($selectedGrade && $selectedYear): ?>
        <?php if (count($examTimetable) > 0): ?>
            <?php
            $currentTitle = '';
            $currentTerm = '';
            $currentDate = ''; // Track dates for merging rows
            ?>
            <?php foreach ($examTimetable as $row): ?>
                <?php if ($row['title'] !== $currentTitle || $row['term'] !== $currentTerm): ?>
                    <?php if ($currentTitle !== '' && $currentTerm !== ''): ?>
                        </tbody></table>
                        <div class="print-button">
                            <button onclick="printTimetable()">Print Timetable</button>
                        </div>
                    </div>
                    <?php endif; ?>
                    <h2><?= htmlspecialchars($row['title']); ?> (Term <?= htmlspecialchars($row['term']); ?>)</h2>
                    <div class="print-section">
                        <table>
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                        <?php
                        $currentTitle = $row['title'];
                        $currentTerm = $row['term'];
                        $currentDate = ''; // Reset the date tracker for each new table
                endif;

                // Format time in AM/PM format
                $formattedTime = date("g:i A", strtotime($row['exam_time']));

                // Merge rows for the same date
                $mergeDate = ($row['exam_date'] === $currentDate) ? '' : $row['exam_date'];
                $currentDate = $row['exam_date']; // Update the current date
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['subject_name']); ?></td>
                    <?php if ($mergeDate): ?>
                        <td rowspan="<?= array_count_values(array_column($examTimetable, 'exam_date'))[$row['exam_date']] ?>">
                            <?= htmlspecialchars($mergeDate); ?>
                        </td>
                    <?php endif; ?>
                    <td><?= htmlspecialchars($formattedTime); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
            <div class="print-button">
                <button onclick="printTimetable()">Print Timetable</button>
            </div>
        </div>
        <?php else: ?>
            <p style="text-align:center;">No exams found for the selected grade and year.</p>
        <?php endif; ?>
    <?php endif; ?>

    <script>
        function printTimetable() {
            const printSection = document.querySelector('.print-section');
            const originalContent = document.body.innerHTML;

            // Replace page content with the timetable for printing
            document.body.innerHTML = printSection.innerHTML;
            window.print();

            // Restore original content
            document.body.innerHTML = originalContent;
            window.location.reload();
        }
    </script>
</body>
</html>

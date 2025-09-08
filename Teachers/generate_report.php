<?php
session_start();

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

// Database connection
include ("db_connection.php");

// Get the teacher's user ID
$teacher_user_id = $_SESSION['user']['username'];

// Fetch the grade_id assigned to the teacher
$stmt = $pdo->prepare("SELECT grade_id FROM teacher WHERE username = :user_id");
$stmt->execute(['user_id' => $teacher_user_id]);
$assigned_grade_id = $stmt->fetchColumn();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Fetch attendance report data
    $stmt = $pdo->prepare("
        SELECT s.name AS student_name, 
               SUM(a.status = 'Present') AS total_present,
               SUM(a.status = 'Absent') AS total_absent
        FROM attendance a
        JOIN students s ON a.student_id = s.id 
        WHERE s.grade_id = :grade_id
        AND a.attendance_date BETWEEN :start_date AND :end_date
        GROUP BY a.student_id
    ");

    $stmt->execute([
        'grade_id' => $assigned_grade_id,
        'start_date' => $start_date,
        'end_date' => $end_date
    ]);
    $attendanceReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        h1, h2 {
            text-align: center;
            color: #333;
            margin: 20px 0;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        label {
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input[type="date"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            width: 100%;
            max-width: 300px;
        }

        button {
            background-color: #4CAF50; /* Green background */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            max-width: 150px;
        }

        button:hover {
            background-color: #45a049; /* Darker green on hover */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50; /* Green background */
            color: white; /* White text */
        }

        tr:hover {
            background-color: #f1f1f1; /* Light grey background on hover */
        }

        .no-records {
            text-align: center;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include('navbar.php'); ?>
    <div class="container">
        <h1>Generate Attendance Report</h1>
        <form method="POST">
            <label for="start_date">Start Date:</label>
            <input type="date" name="start_date" required>
            
            <label for="end_date">End Date:</label>
            <input type="date" name="end_date" required>
            
            <button type="submit">Generate Report</button>
        </form>

        <?php if (isset($attendanceReports) && count($attendanceReports) > 0): ?>
            <h2>Attendance Report from <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></h2>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Total Present</th>
                        <th>Total Absent</th> <!-- Added column for total absent -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendanceReports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($report['total_present']); ?></td>
                            <td><?php echo htmlspecialchars($report['total_absent']); ?></td> <!-- Show total absent -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-records">No attendance records found for the selected date range.</p>
        <?php endif; ?>
    </div>
</body>
</html>

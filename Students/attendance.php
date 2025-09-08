<?php
session_start();

include("db_connection.php");

// Check if the user is logged in and is a Student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

// Assuming the student ID is stored in session
$studentId = $_SESSION['user']['username'];

// Get the first and last date of the current month
$firstDayOfMonth = date("Y-m-01");
$lastDayOfMonth = date("Y-m-t");

// Fetch attendance records for the current month
$sql = "
    SELECT attendance_date, status
    FROM attendance
    WHERE student_id = :student_id
    AND attendance_date BETWEEN :first_day AND :last_day
    ORDER BY attendance_date
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'student_id' => $studentId,
    'first_day' => $firstDayOfMonth,
    'last_day' => $lastDayOfMonth
]);

// Fetch all attendance records
$attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize an array for the month's attendance
$attendanceByDay = [];

// Populate the attendance data for each day
foreach ($attendanceRecords as $record) {
    $formattedDate = date('Y-m-d', strtotime($record['attendance_date']));
    $attendanceByDay[$formattedDate] = $record['status'];
}

// Count totals
$totalPresent = 0;
$totalAbsent = 0;

foreach ($attendanceByDay as $status) {
    if ($status === 'Present') {
        $totalPresent++;
    } elseif ($status === 'Absent') {
        $totalAbsent++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }

        .attendance-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            text-align: center;
            padding: 12px;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #007bff;
            color: white;
        }

        .status-present {
            background-color: #4CAF50;
            color: white;
            padding: 5px;
            border-radius: 5px;
        }

        .status-absent {
            background-color: #FF5733;
            color: white;
            padding: 5px;
            border-radius: 5px;
        }

        .total-summary {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background-color: #f2f2f2;
            border-radius: 8px;
        }

        .total-summary div {
            flex: 1;
            text-align: center;
            padding: 10px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .total-summary .present {
            background-color: #4CAF50;
            color: white;
        }

        .total-summary .absent {
            background-color: #FF5733;
            color: white;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="attendance-container">
    <h2>Your Daily Attendance Record for the Current Month</h2>

    <table>
        <thead>
            <tr>
                <th>Day</th>
                <th>Present</th>
                <th>Absent</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($attendanceByDay as $formattedDate => $status): ?>
                <tr>
                    <td><?= $formattedDate ?></td>
                    <td><?php echo isset($status['Present']) ? '<span class="status-present">Present</span>' : ''; ?></td>
                    <td><?php echo isset($status['Absent']) ? '<span class="status-absent">Absent</span>' : ''; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total-summary">
        <div class="present">
            <strong>Total Present</strong>
            <p><?= $totalPresent ?></p>
        </div>
        <div class="absent">
            <strong>Total Absent</strong>
            <p><?= $totalAbsent ?></p>
        </div>
    </div>
</div>

</body>
</html>

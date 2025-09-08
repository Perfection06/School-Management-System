<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

// Database connection
include ("db_connection.php");

// Get the teacher's username from session
$teacher_user_id = $_SESSION['user']['username'];

// Fetch the attendance history for the assigned class
$attendanceRecords = [];
$attendance_date = $_GET['date'] ?? date('Y-m-d'); // Default to today if no date is selected

$stmt = $pdo->prepare("
    SELECT a.*, s.name AS student_name
    FROM attendance a
    JOIN Students s ON a.student_id = s.id
    WHERE a.teacher_user_id = :teacher_user_id
    AND a.attendance_date = :attendance_date
");
$stmt->execute([
    'teacher_user_id' => $teacher_user_id,
    'attendance_date' => $attendance_date
]);
$attendanceRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        h1 {
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
            justify-content: center;
            margin-bottom: 20px;
        }

        label {
            margin-right: 10px;
            font-weight: bold;
        }

        input[type="date"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }

        button {
            background-color: #4CAF50; /* Green background */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
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

        .actions a {
            color: #007BFF; /* Bootstrap blue */
            text-decoration: none;
            font-weight: bold;
        }

        .actions a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <?php include('navbar.php'); ?>
    <div class="container">
        <h1>Attendance History for <?php echo htmlspecialchars($attendance_date); ?></h1>
        <form method="GET">
            <label for="date">Select Date:</label>
            <input type="date" name="date" value="<?php echo htmlspecialchars($attendance_date); ?>" required>
            <button type="submit">View</button>
        </form>
        <table>
            <tr>
                <th>Student Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php if (count($attendanceRecords) > 0): ?>
                <?php foreach ($attendanceRecords as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['status']); ?></td>
                        <td class="actions">
                            <a href="edit_attendance.php?id=<?php echo $record['id']; ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center;">No attendance records found for this date.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
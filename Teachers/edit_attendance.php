<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user']['username'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit;
}

// Database connection
include ("db_connection.php");


// Fetch specific attendance record with student name for editing
$attendance_id = $_GET['id'];
$stmt = $pdo->prepare("
    SELECT a.*, s.name AS student_name
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE a.id = :id
");
$stmt->execute(['id' => $attendance_id]);
$attendanceRecord = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $updateStmt = $pdo->prepare("UPDATE attendance SET status = :status WHERE id = :id");
    $updateStmt->execute([
        'status' => $status,
        'id' => $attendance_id
    ]);
    echo "<script>
        alert('Attendance updated successfully!');
        window.location.href = 'attendance_history.php';
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Attendance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Edit Attendance for <?php echo htmlspecialchars($attendanceRecord['student_name']); ?></h1>
        <form method="POST">
            <label for="status">Status:</label>
            <select name="status" required>
                <option value="Present" <?php echo $attendanceRecord['status'] == 'Present' ? 'selected' : ''; ?>>Present</option>
                <option value="Absent" <?php echo $attendanceRecord['status'] == 'Absent' ? 'selected' : ''; ?>>Absent</option>
            </select>
            <button type="submit">Update</button>
        </form>
    </div>

</body>
</html>

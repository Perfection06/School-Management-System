<?php


// Check if the user is logged in and is a teacher
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

include("db_connection.php");

// Get the logged-in teacher's username
$teacher_username = $_SESSION['user']['username']; // Use the 'username' field directly

// Fetch today's attendance data for the assigned teacher's class
$today = date('Y-m-d');

$stmt = $pdo->prepare("
    SELECT 
        COUNT(a.id) AS total_attendance, 
        SUM(a.status = 'Present') AS total_present,
        SUM(a.status = 'Absent') AS total_absent
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    JOIN grades g ON s.grade_id = g.id  -- Adjusted to reference the correct foreign key
    WHERE a.attendance_date = :today
    AND a.teacher_user_id = :teacher_username
");

$stmt->execute([
    'today' => $today,
    'teacher_username' => $teacher_username
]);

$report = $stmt->fetch(PDO::FETCH_ASSOC);


?>



<style>
.attendance_body {
    font-family: Arial, sans-serif;
    margin: 20px;
    background-color: #f4f4f4;
}

.card {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    padding: 20px;
    width: 90%; 
    max-width: 600px; 
    transition: transform 0.2s;
    margin-left: -10px;
    margin-top: 50px;
}

.card:hover {
    transform: scale(1.02);
}

.card h2 {
    text-align: center;
    color: #333;
}

.card-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.card-table th, .card-table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.card-table th {
    background-color: #f2f2f2;
    font-weight: bold;
}

.card-table tr:hover {
    background-color: #f9f9f9;
}

.card-table td {
    color: #555;
}
</style>
<body class="attendance_body">
    <div class="card">
        <h2>Daily Attendance Report for <?php echo htmlspecialchars($today); ?></h2>
        <table class="card-table">
            <thead>
                <tr>
                    <th>Total Attendance</th>
                    <th>Total Present</th>
                    <th>Total Absent</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($report['total_attendance']); ?></td>
                    <td><?php echo htmlspecialchars($report['total_present']); ?></td>
                    <td><?php echo htmlspecialchars($report['total_absent']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
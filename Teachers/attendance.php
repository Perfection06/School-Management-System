<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}


// Fetch the username from the session
$username = $_SESSION['user']['username'];  // Assuming username is stored in the session

// Database connection
include ("db_connection.php");

// Fetch the teacher's grade_id from the teacher table
$stmt = $pdo->prepare("SELECT grade_id FROM teacher WHERE username = :username");
$stmt->execute(['username' => $username]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if teacher's grade_id is found
if (!$teacher || !isset($teacher['grade_id'])) {
    // Handle the case where the teacher does not have a grade assigned
    echo "Teacher not found or grade not assigned.";
    exit();
}

$assigned_class_id = $teacher['grade_id'];  // Get grade_id from the teacher's record

// Fetch students in the same grade as the teacher
$stmt = $pdo->prepare("SELECT * FROM Students WHERE grade_id = :grade_id");
$stmt->execute(['grade_id' => $assigned_class_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<style>

    h2 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    th, td {
        padding: 15px;
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

    select {
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 100%;
        box-sizing: border-box;
    }

    button {
        background-color: #4CAF50; /* Green background */
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #45a049; /* Darker green on hover */
    }

    .form-container {
        max-width: 800px;
        margin: auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }

</style>
<?php include('navbar.php'); ?>
<div class="form-container">
    <h2>Mark Attendance</h2>
    <form method="POST" action="process_attendance.php">
        <table>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Status</th>
            </tr>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td><?php echo htmlspecialchars($student['id']); ?></td>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td>
                        <select name="attendance[<?php echo $student['id']; ?>]">
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                        </select>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="submit">Submit Attendance</button>
    </form>
</div>
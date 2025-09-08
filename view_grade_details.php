<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include('database_connection.php');

// Validate the grade ID from the URL
$grade_id = isset($_GET['grade_id']) ? intval($_GET['grade_id']) : null;
if ($grade_id === null) {
    echo "Invalid grade ID.";
    exit;
}

// Fetch the grade name for the given grade_id
$grade_query = "SELECT grade_name FROM grades WHERE id = ?";
$grade_stmt = $conn->prepare($grade_query);
$grade_stmt->bind_param("i", $grade_id);
$grade_stmt->execute();
$grade_stmt->bind_result($grade_name);
$grade_found = $grade_stmt->fetch();
$grade_stmt->close();

if (!$grade_found || empty($grade_name)) {
    echo "Grade not found.";
    exit;
}

// Fetch the assigned teacher for this grade_id (it might be null)
$teacher_query = "
    SELECT t.profile_image, t.full_name, t.username
    FROM teacher t
    JOIN user u ON t.username = u.username
    WHERE t.grade_id = ?";
$teacher_stmt = $conn->prepare($teacher_query);
$teacher_stmt->bind_param("i", $grade_id);
$teacher_stmt->execute();
$teacher_result = $teacher_stmt->get_result();
$teacher = $teacher_result->fetch_assoc(); // Fetch once for the teacher details
$teacher_stmt->close();

// Fetch students based on the grade_id, joining with Student_Admissions for image retrieval
$students_query = "
    SELECT s.id, s.name, COALESCE(sa.student_image, './Resources/default_profile.png') AS student_image
    FROM Students s
    LEFT JOIN Student_Admissions sa ON s.id = sa.id
    WHERE s.grade_id = ?";
$students_stmt = $conn->prepare($students_query);
$students_stmt->bind_param("i", $grade_id);  // Use grade_id, not grade_name
$students_stmt->execute();
$students_result = $students_stmt->get_result();
$students_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Details</title>
    <style>
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .card {
            background-color: #f4f7f6;
            color: #333;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 150px;
            text-align: center;
            padding: 15px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }
        .card p {
            color: #333;
            font-weight: bold;
            margin: 0;
        }
        .no-data {
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <h2>Grade Details</h2>

    <h3>Assigned Teacher</h3>
    <div class="card-container">
        <?php if ($teacher): ?>
            <!-- Link to teacher profile page -->
            <a href="./teacher_profile.php?username=<?php echo urlencode($teacher['username']); ?>" class="card">
                <?php if (!empty($teacher['profile_image']) && file_exists($teacher['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($teacher['profile_image']); ?>" alt="Teacher Image">
                <?php else: ?>
                    <img src="./Resources/default_profile.png" alt="No Image">
                <?php endif; ?>
                <p><?php echo htmlspecialchars($teacher['full_name']); ?></p>
            </a>
        <?php else: ?>
            <!-- Leave the teacher section empty or show "No teacher assigned" without the entire "No teacher assigned" message -->
            <p class="no-data">No teacher assigned to this grade.</p>
        <?php endif; ?>
    </div>

    <h3>Students</h3>
    <div class="card-container">
        <?php if ($students_result->num_rows > 0): ?>
            <?php while ($student = $students_result->fetch_assoc()): ?>
                <!-- Link to student profile page -->
                <a href="student_profile.php?id=<?php echo urlencode($student['id']); ?>" class="card">
                    <?php if (!empty($student['student_image']) && file_exists($student['student_image'])): ?>
                        <img src="<?php echo htmlspecialchars($student['student_image']); ?>" alt="Student Image">
                    <?php else: ?>
                        <img src="./Resources/default_profile.png" alt="No Image">
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($student['name']); ?></p>
                </a>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No students found for this grade.</p>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
// Close the database connection
$conn->close();
?>

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

// Fetch grade name to display
$grade_query = "SELECT grade_name FROM grades WHERE id = ?";
$grade_stmt = $conn->prepare($grade_query);
$grade_stmt->bind_param("i", $grade_id);  // Bind as integer since grade_id is likely an integer
$grade_stmt->execute();
$grade_stmt->bind_result($grade_name);
$grade_found = $grade_stmt->fetch();
$grade_stmt->close();

if (!$grade_found || empty($grade_name)) {
    echo "Grade not found.";
    exit;
}

// Fetch students based on the grade_id, joining with Student_Admissions for image retrieval
$students_query = "
    SELECT s.id, s.name, s.active, COALESCE(sa.student_image, './Resources/default_profile.png') AS student_image
    FROM Students s
    LEFT JOIN Student_Admissions sa ON s.id = sa.id
    WHERE s.grade_id = ?";  

$students_stmt = $conn->prepare($students_query);
$students_stmt->bind_param("i", $grade_id);  // Bind grade_id as integer
$students_stmt->execute();
$students_result = $students_stmt->get_result();
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
.card.inactive {
    filter: blur(1px); /* Reduced blur for a subtler effect */
    position: relative;
}
.card.inactive::after {
    content: "Blocked";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 0, 0, 0.8);
    color: #fff;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: bold;
}
.no-data {
    color: #888;
    text-align: center;
}

    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <h3>Students in Grade: <?php echo htmlspecialchars($grade_name); ?></h3>
<div class="card-container">
    <?php if ($students_result->num_rows > 0): ?>
        <?php while ($student = $students_result->fetch_assoc()): ?>
            <!-- Link to student profile page -->
            <a href="student_profile.php?id=<?php echo urlencode($student['id']); ?>" class="card <?php echo $student['active'] == 0 ? 'inactive' : ''; ?>">
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

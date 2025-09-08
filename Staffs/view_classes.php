<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Staff') {
    header("Location: login.php");
    exit;
}


include('database_connection.php');

// Fetch grades from the database
$grades_query = "SELECT * FROM grades";
$grades_result = $conn->query($grades_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Classes</title>
    <style>
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .card {
            background-color: #007acc;
            color: white;
            padding: 20px;
            border-radius: 10px;
            width: 200px;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .card:hover {
            background-color: #005f99;
        }
    </style>
</head>
<body>
    
<?php include('navbar.php'); ?>
    <h2>Select a Grade</h2>
    <div class="card-container">
        <?php while ($grade = $grades_result->fetch_assoc()): ?>
            <a href="view_grade_details.php?grade_id=<?php echo $grade['id']; ?>" class="card">
                <h3><?php echo htmlspecialchars($grade['grade_name']); ?></h3>
            </a>
        <?php endwhile; ?>
    </div>
</body>
</html>

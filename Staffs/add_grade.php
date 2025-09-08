<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Staff') {
    header("Location: login.php");
    exit;
}

// Database connection
include("database_connection.php");

$errorMessage = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $grade_name = $_POST['grade_name'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO grades (grade_name) VALUES (?)");
    $stmt->bind_param("s", $grade_name);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<script>alert('Grade Added!'); window.location.href = './add_grade.php';</script>";
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all grades
$result = $conn->query("SELECT grade_name FROM grades");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Grade</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            justify-content: center;
            margin-right: 200px; /* Space for sidebar on the right */
        }

        /* Sidebar for grades list */
        .grades-sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: 180px;
            padding: 20px;
            background-color: #f4f4f4;
            border-left: 1px solid #ddd;
            height: 100%;
        }

        .grades-sidebar h2 {
            margin-top: 0;
        }

        .grades-list {
            list-style-type: none;
            padding: 0;
        }

        .grades-list li {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .form-container {
            max-width: 600px;
            padding: 50px;
            background-color: #fff;
            border: 1px solid #ddd;
            margin-top: 50px;
        }

        input[type="text"], input[type="submit"] {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<!-- Floating grades list on the right -->
<div class="grades-sidebar">
    <h2>Grades</h2>
    <ul class="grades-list">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($row['grade_name']); ?></li>
            <?php endwhile; ?>
        <?php else: ?>
            <li>No grades available</li>
        <?php endif; ?>
    </ul>
</div>

<!-- Form content in the center -->
<div class="container">
    <div class="form-container">
        <h2>Add New Grade</h2>
        <form action="add_grade.php" method="post">
            <label for="grade_name">Enter the grade:</label>
            <input type="text" name="grade_name" id="grade_name" required>
            <input type="submit" value="Add Grade">
        </form>
        <?php if ($errorMessage): ?>
            <p style="color:red;"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

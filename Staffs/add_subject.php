<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Staff') {
    header("Location: login.php");
    exit;
}
// Database connection
include ("database_connection.php");

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject_name = $_POST['subject_name'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO subjects (subject_name) VALUES (?)");
    $stmt->bind_param("s", $subject_name);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<script>alert('Subject Added!'); window.location.href = './add_subject.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all subjects
$result = $conn->query("SELECT subject_name FROM subjects");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject</title>
    <link rel="stylesheet" href="./css/add_subject.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            justify-content: center;
            background-color: transparent;
        }

        /* Floating the subjects on the right side */
        .subject-sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: 180px;
            padding: 20px;
            background-color: #f4f4f4;
            border-left: 1px solid #ddd;
            height: 100%;
        }

        .subject-sidebar h2 {
            margin-top: 0;
        }

        .subject-list {
            list-style-type: none;
            padding: 0;
        }

        .subject-list li {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .form-container {
            max-width: 600px;
            padding: 20px;
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
<!-- Floating subjects list on the right -->
<div class="subject-sidebar">
    <h2>Subjects</h2>
    <ul class="subject-list">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($row['subject_name']); ?></li>
            <?php endwhile; ?>
        <?php else: ?>
            <li>No subjects available</li>
        <?php endif; ?>
    </ul>
</div>

<!-- Form content in the center -->
<div class="container">
    <div class="form-container">
        <h2>Add New Subject</h2>
        <form action="./add_subject.php" method="post">
            <label for="subject_name">Enter the subject:</label>
            <input type="text" name="subject_name" id="subject_name" required>
            <input type="submit" value="Add Subject">
        </form>
    </div>
</div>

</body>
</html>

<?php
session_start();

include("db_connection.php");

// Check if the user is logged in and is a Student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

// Fetch student data using student ID from session
$studentId = $_SESSION['user']['username'];

// Fetch the student's grade ID from the Students table
$stmt = $pdo->prepare("SELECT grade_id FROM Students WHERE username = :username");
$stmt->execute(['username' => $studentId]);
$studentGradeId = $stmt->fetchColumn();

if (!$studentGradeId) {
    echo "Student's grade not found.";
    exit();
}

// Fetch assignments or study materials for the student's grade
$stmt = $pdo->prepare("
    SELECT title, file_path, end_date 
    FROM assignments 
    WHERE class_id = :grade_id 
    AND end_date >= CURDATE()
");
$stmt->execute(['grade_id' => $studentGradeId]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments and Study Materials</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f1f5f8;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 70%;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .container h2 {
            text-align: center;
            font-size: 2em;
            margin-bottom: 30px;
            color: #333;
        }

        .assignment-list {
            list-style-type: none;
            padding: 0;
        }

        .assignment-item {
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .assignment-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .assignment-item a {
            color: #007BFF;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
        }

        .assignment-item a:hover {
            text-decoration: underline;
            color: #0056b3;
        }

        .assignment-item small {
            color: #555;
            font-size: 0.9em;
        }

        .no-assignments {
            text-align: center;
            color: #888;
            font-size: 1.2em;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 20px;
            }

            .container h2 {
                font-size: 1.8em;
            }

            .assignment-item {
                padding: 15px;
            }
        }
        /* Responsive Styles */
/* Large Laptops and Desktops (1200px and above) */
@media (max-width: 1200px) {
    .container {
        width: 80%;
    }

    .assignment-item {
        padding: 18px;
    }
}

/* Tablets and Small Laptops (768px to 1199px) */
@media (max-width: 1199px) {
    .container {
        width: 90%;
    }

    .container h2 {
        font-size: 1.8em;
    }

    .assignment-item {
        padding: 15px;
    }
}

/* Mobile Phones (767px and below) */
@media (max-width: 767px) {
    .container {
        width: 95%;
        padding: 15px;
    }

    .container h2 {
        font-size: 1.5em;
    }

    .assignment-item {
        padding: 12px;
    }

    .assignment-item a {
        font-size: 0.95em;
    }

    .assignment-item small {
        font-size: 0.85em;
    }
}
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container">
    <h2>Assignments and Study Materials</h2>

    <?php if (count($assignments) > 0): ?>
        <ul class="assignment-list">
            <?php foreach ($assignments as $assignment): ?>
                <li class="assignment-item">
                    <strong><?php echo htmlspecialchars($assignment['title']); ?></strong><br>
                    <a href="<?php echo $assignment['file_path']; ?>" download>Download</a><br>
                    <small>Available until: <?php echo $assignment['end_date']; ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="no-assignments">No assignments or study materials available for your grade at this time.</p>
    <?php endif; ?>
</div>

</body>
</html>

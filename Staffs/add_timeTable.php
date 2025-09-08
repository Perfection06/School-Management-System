<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Staff') {
    header("Location: login.php");
    exit;
}
include ("database_connection.php");

// Fetch all grades for the cards
$grades = [];
$grades_sql = "SELECT id, grade_name FROM grades ORDER BY grade_name ASC";
if ($stmt = $conn->prepare($grades_sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    $stmt->close();
} else {
    die("Failed to fetch grades: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Class - Time Table System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 40px;
        }
        .hheader {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h2 {
            color: #333;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .card {
            background-color: #fff;
            width: 200px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: inherit;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0,0,0,0.2);
        }
        .card h3 {
            margin: 0;
            color: #3498db;
        }
        @media (max-width: 600px) {
            .card-container {
                flex-direction: column;
                align-items: center;
            }
            .card {
                width: 80%;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="hheader">
        <h2>Select a Class to Manage Time Table</h2>
    </div>
    <div class="card-container">
        <?php if (!empty($grades)): ?>
            <?php foreach ($grades as $grade): ?>
                <a class="card" href="timetable.php?grade_id=<?php echo urlencode($grade['id']); ?>">
                    <h3><?php echo htmlspecialchars($grade['grade_name']); ?></h3>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No classes available.</p>
        <?php endif; ?>
    </div>
</body>
</html>

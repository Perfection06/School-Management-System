<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include("db_connection.php");

// Fetch all subjects
$querySubjects = "SELECT * FROM subjects ORDER BY subject_name";
$stmtSubjects = $pdo->query($querySubjects);
$subjects = $stmtSubjects->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subjects</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .card-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 50px;
        }
        .card {
            width: 200px;
            padding: 20px;
            border: 1px solid #ddd;
            text-align: center;
            border-radius: 5px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <h1 style="text-align: center;">Subjects</h1>
    <div class="card-container">
        <?php foreach ($subjects as $subject): ?>
            <div class="card" onclick="window.location.href='subject_progress.php?subject_id=<?= $subject['id'] ?>'">
                <?= htmlspecialchars($subject['subject_name']) ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>

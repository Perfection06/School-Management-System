<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

include("db_connection.php");

// Fetch active notices
$currentDate = date("Y-m-d");
$sql = "SELECT title, content, end_date FROM notices WHERE end_date >= ? ORDER BY end_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$currentDate]);
$notices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notices</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .notice-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .notice-item:last-child {
            border-bottom: none;
        }
        .notice-item strong {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .notice-content {
            margin-bottom: 10px;
            color: #666;
        }
        .notice-date {
            font-size: 0.9em;
            color: #999;
        }
        p {
            text-align: center;
            color: #999;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container">
    <h2>Notices</h2>
    <?php if (!empty($notices)): ?>
        <?php foreach ($notices as $notice): ?>
            <div class="notice-item">
                <strong>Title:</strong> <?php echo htmlspecialchars($notice['title']); ?>
                <div class="notice-content"><?php echo nl2br(htmlspecialchars($notice['content'])); ?></div>
                <div class="notice-date"><strong>End Date:</strong> <?php echo date("M d, Y", strtotime($notice['end_date'])); ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No active notices at the moment.</p>
    <?php endif; ?>
</div>

</body>
</html>

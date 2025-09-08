<?php
session_start();

include("db_connection.php");

// Check if the user is logged in and is a Student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

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
        /* Styling similar to admin portal */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #333;
        }
        .container {
            max-width: 600px; /* Default for larger screens */
            width: 100%;      /* Always take full width of the parent */
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin: 20px auto;
        }
        h2 {
            text-align: center;
            color: #3498db;
            margin-bottom: 20px;
        }
        .notice-item {
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .notice-item strong {
            color: #2c3e50;
        }
        .notice-content {
            color: #555;
            margin: 10px 0;
            white-space: pre-wrap;
        }
        .notice-date {
            font-size: 0.9em;
            color: #888;
        }

        /* Responsive Styles */
/* Large Laptops and Desktops (1200px and above) */
@media (max-width: 1200px) {
    .container {
        max-width: 80%;
    }

    h2 {
        font-size: 1.8em;
    }
}

/* Tablets and Small Laptops (768px to 1199px) */
@media (max-width: 1199px) {
    .container {
        max-width: 90%;
    }

    h2 {
        font-size: 1.6em;
    }

    .notice-item {
        padding: 12px;
    }
}

/* Mobile Phones (767px and below) */
@media (max-width: 767px) {
    .container {
        max-width: 95%;
        padding: 15px;
    }

    h2 {
        font-size: 1.5em;
    }

    .notice-item {
        padding: 10px;
    }

    .notice-item strong {
        font-size: 1em;
    }

    .notice-content {
        font-size: 0.95em;
    }

    .notice-date {
        font-size: 0.85em;
    }
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

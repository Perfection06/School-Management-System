<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}


// Connect to the database
include ("db_connection.php");

// Handle form submission for creating a new notice
$alertMessage = ''; // Initialize alert message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_notice'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $endDate = $_POST['end_date'];

    // Insert the notice into the database
    $sql = "INSERT INTO notices (title, content, end_date) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$title, $content, $endDate])) {
        $alertMessage = "Notice created successfully!";
    } else {
        $alertMessage = "Failed to create notice. Please try again.";
    }
}

// Handle notice deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notice_id'])) {
    $noticeId = $_POST['delete_notice_id'];

    $deleteSql = "DELETE FROM notices WHERE id = ?";
    $deleteStmt = $pdo->prepare($deleteSql);
    if ($deleteStmt->execute([$noticeId])) {
        $alertMessage = "Notice deleted successfully!";
    } else {
        $alertMessage = "Failed to delete notice. Please try again.";
    }
}

// Automatically delete expired notices
$currentDate = date("Y-m-d");
$deleteSql = "DELETE FROM notices WHERE end_date < ?";
$deleteStmt = $pdo->prepare($deleteSql);
$deleteStmt->execute([$currentDate]);

// Retrieve all active notices
$noticesSql = "SELECT id, title, content, end_date FROM notices WHERE end_date >= ? ORDER BY end_date ASC";
$noticesStmt = $pdo->prepare($noticesSql);
$noticesStmt->execute([$currentDate]);
$notices = $noticesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Notice</title>
    <style>
        /* Reset some basic styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f4f6f8;
            color: #333;
        }

        /* Centered Container */
        .container {
            max-width: 600px;
            width: 100%;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        /* Title styling */
        h2 {
            margin-bottom: 20px;
            color: #3498db;
            text-align: center;
        }

        /* Form styling */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-weight: bold;
            color: #333;
        }
        input[type="text"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button[type="submit"] {
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button[type="submit"]:hover {
            background-color: #2980b9;
        }

        /* Notices styling */
        .notices {
            margin-top: 30px;
        }
        .notice-item {
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .notice-item:hover {
            transform: scale(1.02);
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
        form.delete-form {
            display: inline;
        }
        .delete-button {
            padding: 5px 10px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.9em;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        .delete-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container">
    <h2>Create a New Notice</h2>
    <form method="POST">
        <label for="title">Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="content">Content:</label>
        <textarea name="content" id="content" rows="5" required></textarea>

        <label for="end_date">End Date:</label>
        <input type="date" name="end_date" id="end_date" required>

        <button type="submit" name="create_notice">Create Notice</button>
    </form>

    <div class="notices">
        <h2>Current Notices</h2>
        <?php if (!empty($notices)): ?>
            <?php foreach ($notices as $notice): ?>
                <div class="notice-item">
                    <strong>Title:</strong> <?php echo htmlspecialchars($notice['title']); ?>
                    <div class="notice-content"><?php echo nl2br(htmlspecialchars($notice['content'])); ?></div>
                    <div class="notice-date"><strong>End Date:</strong> <?php echo date("M d, Y", strtotime($notice['end_date'])); ?></div>
                    <form method="POST" class="delete-form">
                        <input type="hidden" name="delete_notice_id" value="<?php echo $notice['id']; ?>">
                        <button type="submit" class="delete-button">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No active notices at the moment.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

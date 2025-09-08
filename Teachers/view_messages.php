<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

include('database_connection.php');

// Get the logged-in teacher's username
$username = $_SESSION['user']['username'];

// Fetch Individual Messages (personal messages sent to the teacher)
$individual_query = "SELECT sender_username, content, attachment_path, timestamp 
                     FROM messages 
                     WHERE receiver_username = ? AND is_broadcast = 0 
                     ORDER BY timestamp DESC";
$individual_stmt = $conn->prepare($individual_query);
$individual_stmt->bind_param('s', $username);
$individual_stmt->execute();
$individual_result = $individual_stmt->get_result();

// Fetch Broadcast Messages (messages sent to all teachers)
$broadcast_query = "SELECT sender_username, content, attachment_path, timestamp 
                    FROM messages 
                    WHERE is_broadcast = 1 AND target_group = 'Teachers' 
                    ORDER BY timestamp DESC";
$broadcast_result = $conn->query($broadcast_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Messages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            margin: 20px auto;
            max-width: 800px;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1, h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        td {
            background-color: #f4f4f4;
        }
        .btn-back {
            display: inline-block;
            margin-bottom: 15px;
            text-decoration: none;
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
        }
        .btn-back:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="container">
        <h1>Inbox</h1>

        <!-- Personal Messages Section -->
        <div>
            <h2>Personal Messages</h2>
            <table>
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Attachment</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($individual_result->num_rows > 0): ?>
                        <?php while ($row = $individual_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                                <td>
                                    <?php if ($row['attachment_path']): ?>
                                        <a href="<?= htmlspecialchars($row['attachment_path']) ?>" target="_blank">Download</a>
                                    <?php else: ?>
                                        None
                                    <?php endif; ?>
                                </td>
                                <td><?= date('m/d/Y h:i A', strtotime($row['timestamp'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No personal messages found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Broadcast Messages Section -->
        <div>
            <h2>Broadcast Messages</h2>
            <table>
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Attachment</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($broadcast_result->num_rows > 0): ?>
                        <?php while ($row = $broadcast_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                                <td>
                                    <?php if ($row['attachment_path']): ?>
                                        <a href="<?= htmlspecialchars($row['attachment_path']) ?>" target="_blank">Download</a>
                                    <?php else: ?>
                                        None
                                    <?php endif; ?>
                                </td>
                                <td><?= date('m/d/Y h:i A', strtotime($row['timestamp'])) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No broadcast messages found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

include('database_connection.php');

// Get logged-in student's username
$username = $_SESSION['user']['username'];

// Fetch Individual Messages (messages sent directly to the student)
$individual_query = "SELECT sender_username, content, attachment_path, timestamp 
                     FROM student_send_messages 
                     WHERE receiver_username = ? 
                     ORDER BY timestamp DESC";
$individual_stmt = $conn->prepare($individual_query);
$individual_stmt->bind_param('s', $username);
$individual_stmt->execute();
$individual_result = $individual_stmt->get_result();

// Fetch Broadcast Messages (messages sent to all students)
$broadcast_query = "SELECT sender_username, content, attachment_path, timestamp 
                    FROM messages 
                    WHERE is_broadcast = 1 AND target_group = 'Students' 
                    ORDER BY timestamp DESC";
$broadcast_result = $conn->query($broadcast_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Inbox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #F0F4F8; /* Light blue-gray */
        }
        .container {
            margin-top: 30px;
        }
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            border: none;
        }
        .card-header {
            background-color: #4A89DC; /* Mid-light blue */
            color: white;
            font-weight: bold;
        }
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }
        .table thead {
            background-color: #3C6382; /* Dark blue */
            color: white;
        }
        .btn-download {
            background-color: #5D9CEC; /* Light blue */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn-download:hover {
            background-color: #4A89DC;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container">
    <h2 class="text-center mb-4 text-primary">Inbox</h2>

    <!-- Personal Messages Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Personal Messages</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Message</th>
                            <th>Attachment</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($individual_result->num_rows > 0): ?>
                            <?php while ($row = $individual_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['sender_username']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                                    <td>
                                        <?php if ($row['attachment_path']): ?>
                                            <a href="<?= htmlspecialchars($row['attachment_path']) ?>" target="_blank" class="btn-download">Download</a>
                                        <?php else: ?>
                                            None
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('m/d/Y h:i A', strtotime($row['timestamp'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No personal messages found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Broadcast Messages Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Broadcast Messages</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Sender</th>
                            <th>Message</th>
                            <th>Attachment</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($broadcast_result->num_rows > 0): ?>
                            <?php while ($row = $broadcast_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['sender_username']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                                    <td>
                                        <?php if ($row['attachment_path']): ?>
                                            <a href="<?= htmlspecialchars($row['attachment_path']) ?>" target="_blank" class="btn-download">Download</a>
                                        <?php else: ?>
                                            None
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('m/d/Y h:i A', strtotime($row['timestamp'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">No broadcast messages found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

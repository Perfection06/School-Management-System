<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include('database_connection.php');

// Fetch all messages from `user_messages`
$user_query = "
    SELECT um.id, um.sender_username, um.content, um.attachment_path, um.target_role, um.timestamp, u.role, um.is_read
    FROM user_messages um
    JOIN user u ON um.sender_username = u.username
    ORDER BY um.timestamp DESC
";
$user_result = $conn->query($user_query);

// Fetch all messages from `student_messages`
$student_query = "
    SELECT sm.id, sm.sender_username, sm.content, sm.attachment_path, sm.grade_id, sm.timestamp, sm.is_read, g.grade_name
    FROM student_messages sm
    JOIN grades g ON sm.grade_id = g.id
    ORDER BY sm.timestamp DESC
";
$student_result = $conn->query($student_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Messages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
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
        .section {
            margin-bottom: 30px;
        }
        .btn-read, .btn-delete {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-read {
            background-color: #007BFF;
            color: white;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .faded {
            opacity: 0.6;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<div class="container">
    <h1>Messages</h1>

    <!-- Section: Student Messages -->
    <div class="section">
        <h2>Student Messages</h2>
        <table>
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Grade</th>
                    <th>Message</th>
                    <th>Actions</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($student_result && $student_result->num_rows > 0): ?>
                    <?php while ($row = $student_result->fetch_assoc()): ?>
                        <tr id="row-student-<?= $row['id'] ?>" class="<?= $row['is_read'] ? 'faded' : '' ?>">
                            <td><?= htmlspecialchars($row['sender_username']) ?></td>
                            <td><?= htmlspecialchars($row['grade_name']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                            <td>
                                <button class="btn btn-read" 
                                        onclick="markAsRead('<?= $row['id'] ?>', 'student')">Mark as Read</button>
                                <button class="btn btn-delete" 
                                        onclick="deleteMessage('<?= $row['id'] ?>', 'student')">Delete</button>
                            </td>
                            <td><?= date("m/d/Y h:i A", strtotime($row['timestamp'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No messages from students.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Section: User Messages -->
    <div class="section">
        <h2>User Messages</h2>
        <table>
            <thead>
                <tr>
                    <th>Sender</th>
                    <th>Role</th>
                    <th>Message</th>
                    <th>Actions</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($user_result && $user_result->num_rows > 0): ?>
                    <?php while ($row = $user_result->fetch_assoc()): ?>
                        <tr id="row-user-<?= $row['id'] ?>" class="<?= $row['is_read'] ? 'faded' : '' ?>">
                            <td><?= htmlspecialchars($row['sender_username']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                            <td>
                                <button class="btn btn-read" 
                                        onclick="markAsRead('<?= $row['id'] ?>', 'user')">Mark as Read</button>
                                <button class="btn btn-delete" 
                                        onclick="deleteMessage('<?= $row['id'] ?>', 'user')">Delete</button>
                            </td>
                            <td><?= date("m/d/Y h:i A", strtotime($row['timestamp'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No messages from users.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function markAsRead(id, type) {
        // Send request to mark message as read
        fetch(`mark_as_read.php?id=${id}&type=${type}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add 'faded' class to indicate the message is read
                    document.getElementById(`row-${type}-${id}`).classList.add('faded');
                } else {
                    alert('Error marking message as read.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
            });
    }

    function deleteMessage(id, type) {
        if (confirm('Are you sure you want to delete this message?')) {
            fetch(`delete_message.php?id=${id}&type=${type}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`row-${type}-${id}`).remove();
                    } else {
                        alert('Error deleting message.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred.');
                });
        }
    }
</script>

</body>
</html>

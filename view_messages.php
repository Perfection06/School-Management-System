<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Database connection
include("database_connection.php");

// Fetch all messages (both from messages & student_send_messages)
$query = "
    SELECT 
        m.id,
        m.sender_username,
        m.receiver_username,
        m.content,
        m.attachment_path,
        m.is_broadcast,
        m.target_group,
        m.grade_id,
        m.timestamp,
        m.is_read,
        'Messages' AS message_source, -- Distinguish between tables
        u.role AS receiver_role,
        CASE
            WHEN u.role = 'Teacher' THEN t.full_name
            WHEN u.role = 'NoClass_Teacher' THEN nt.full_name
            WHEN u.role = 'Staff' THEN s.full_name
            ELSE 'Unknown'
        END AS receiver_name
    FROM 
        messages m
    LEFT JOIN user u ON m.receiver_username = u.username
    LEFT JOIN teacher t ON u.role = 'Teacher' AND m.receiver_username = t.username
    LEFT JOIN noclass_teacher nt ON u.role = 'NoClass_Teacher' AND m.receiver_username = nt.username
    LEFT JOIN staff s ON u.role = 'Staff' AND m.receiver_username = s.username

    UNION ALL

    SELECT 
        sm.id,
        sm.sender_username,
        sm.receiver_username,
        sm.content,
        sm.attachment_path,
        sm.is_broadcast,
        'Students' AS target_group,
        sm.grade_id,
        sm.timestamp,
        NULL AS is_read,
        'Student Messages' AS message_source, -- Identify student messages
        'Student' AS receiver_role,
        st.name AS receiver_name
    FROM 
        student_send_messages sm
    LEFT JOIN students st ON sm.receiver_username = st.username
    ORDER BY 
        timestamp DESC
";
$result = mysqli_query($conn, $query);

// Handle message deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query1 = "DELETE FROM messages WHERE id = $delete_id";
    $delete_query2 = "DELETE FROM student_send_messages WHERE id = $delete_id";
    
    // Attempt deletion in both tables
    mysqli_query($conn, $delete_query1);
    mysqli_query($conn, $delete_query2);

    header("Location: " . $_SERVER['PHP_SELF']); // Refresh the page after deletion
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Messages</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            font-size: 2.5em;
            margin-bottom: 20px;
        }

        .message-section {
            margin-bottom: 40px;
        }

        .message-section h2 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 10px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Broadcast and Individual Rows */
        .broadcast {
            background-color: #e7f4ff;
        }

        .individual {
            background-color: #fff8e1;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            table {
                font-size: 14px;
            }

            th, td {
                padding: 10px;
            }

            h1 {
                font-size: 2em;
            }

            .message-section h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<h1>All Messages</h1>

<!-- Broadcast Messages Section -->
<div class="message-section">
    <h2>Broadcast Messages</h2>
    <table>
        <thead>
            <tr>
                <th>Sender</th>
                <th>Target Group</th>
                <th>Grade</th>
                <th>Content</th>
                <th>Timestamp</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { 
                if ($row['is_broadcast'] == 1) { ?>
                    <tr class="broadcast">
                        <td><?php echo $row['sender_username']; ?></td>
                        <td><?php echo $row['target_group']; ?></td>
                        <td><?php echo $row['grade_id']; ?></td>
                        <td><?php echo $row['content']; ?></td>
                        <td><?php echo date('F j, Y, g:i A', strtotime($row['timestamp'])); ?></td>
                        <td><a href="?delete_id=<?php echo $row['id']; ?>" class="delete-btn">Delete</a></td>
                    </tr>
                <?php } ?>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Individual Messages Section -->
<div class="message-section">
    <h2>Individual Messages</h2>
    <table>
        <thead>
            <tr>
                <th>Sender</th>
                <th>Receiver</th>
                <th>Receiver Name</th>
                <th>Receiver Role</th>
                <th>Content</th>
                <th>Timestamp</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                // Reset the result pointer to fetch again for individual messages
                mysqli_data_seek($result, 0);
                while ($row = mysqli_fetch_assoc($result)) { 
                    if ($row['is_broadcast'] == 0) { ?>
                        <tr class="individual">
                            <td><?php echo $row['sender_username']; ?></td>
                            <td><?php echo $row['receiver_username']; ?></td>
                            <td><?php echo $row['receiver_name']; ?></td>
                            <td><?php echo $row['receiver_role']; ?></td>
                            <td><?php echo $row['content']; ?></td>
                            <td><?php echo date('F j, Y, g:i A', strtotime($row['timestamp'])); ?></td>
                            <td><a href="?delete_id=<?php echo $row['id']; ?>" class="delete-btn">Delete</a></td>
                        </tr>
                    <?php } 
                }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
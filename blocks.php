<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'database_connection.php';

// Fetch all block reasons with full names
$sql_view_blocks = "
    SELECT 
    b.username, 
    COALESCE(t.full_name, nt.full_name, s.full_name, a.full_name) AS full_name, 
    b.block_reason, 
    b.block_date, 
    COALESCE(u.role, 'Accountant') AS role 
    FROM 
        block_reasons b 
    JOIN 
        user u ON b.username = u.username
    LEFT JOIN 
        teacher t ON u.username = t.username
    LEFT JOIN 
        noclass_teacher nt ON u.username = nt.username
    LEFT JOIN 
        staff s ON u.username = s.username
    LEFT JOIN 
        accountant a ON b.username = a.username

    UNION

    SELECT 
        ab.username, 
        a.full_name, 
        ab.block_reason, 
        ab.block_date, 
        'Accountant' AS role 
    FROM 
        accountant_block_reasons ab 
    JOIN 
        accountant a ON ab.username = a.username

    ORDER BY block_date DESC";

$result = $conn->query($sql_view_blocks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blocked Users and Reasons</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 90%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .no-data {
            text-align: center;
            margin: 20px;
            font-size: 1.2em;
            color: #555;
        }
        .btn-unblock {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 3px;
        }
        .btn-unblock:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <h1>Blocked Users and Reasons</h1>

    <?php
    if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Role</th>
                    <th>Reason</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td><?php echo htmlspecialchars($row['block_reason']); ?></td>
                        <td><?php echo htmlspecialchars($row['block_date']); ?></td>
                        <td>
                            <form action="unblock_user.php" method="POST" style="display:inline;">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($row['username']); ?>">
                                <input type="hidden" name="role" value="<?php echo htmlspecialchars($row['role']); ?>">
                                <button type="submit" class="btn-unblock">Unblock</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No blocked users found.</p>
    <?php endif;

    // Close the database connection
    $conn->close();
    ?>
</body>
</html>

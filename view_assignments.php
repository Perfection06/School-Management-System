<?php
// Start the session
session_start();

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Database connection
include("db_connection.php");

// Fetch all assignments with teacher and class information
$sql = "
    SELECT a.id, a.title, a.file_path, a.end_date, u.role, u.username, 
           COALESCE(t.full_name, nct.full_name, s.full_name) AS user_name, 
           g.grade_name AS class_name
    FROM assignments a
    LEFT JOIN user u ON a.username = u.username
    LEFT JOIN teacher t ON u.username = t.username
    LEFT JOIN noclass_teacher nct ON u.username = nct.username
    LEFT JOIN staff s ON u.username = s.username
    LEFT JOIN grades g ON a.class_id = g.id
    ORDER BY a.end_date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }
        td {
            border-bottom: 1px solid #ddd;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        @media screen and (max-width: 768px) {
            table {
                font-size: 14px;
            }
            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<h2>Assignments Given by Users</h2>

<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Role</th>
            <th>Username</th>
            <th>Name</th>
            <th>Class</th>
            <th>End Date</th>
            <th>Assignment File</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Check if there are any assignments
        if (count($assignments) > 0) {
            // Output data for each assignment
            foreach ($assignments as $assignment) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($assignment['title']) . "</td>";
                echo "<td>" . htmlspecialchars($assignment['role']) . "</td>"; // Role (Teacher/Staff)
                echo "<td>" . htmlspecialchars($assignment['username']) . "</td>"; // Username
                echo "<td>" . htmlspecialchars($assignment['user_name']) . "</td>"; // User's full name
                echo "<td>" . htmlspecialchars($assignment['class_name']) . "</td>";
                echo "<td>" . htmlspecialchars($assignment['end_date']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($assignment['file_path']) . "' target='_blank'>Download</a></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No assignments found.</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>

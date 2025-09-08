<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include('database_connection.php');

// Fetch blocked students
$query = "
    SELECT 
        s.id AS student_id, 
        s.name AS student_name, 
        s.username, 
        sb.block_reason, 
        sb.blocked_at
    FROM students s
    JOIN student_block_reasons sb ON s.id = sb.student_id
    WHERE s.active = 0
";

$result = $conn->query($query);

if (!$result) {
    die("Error fetching blocked students: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blocked Students</title>
    <!-- Bootstrap CSS Link -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Optional custom stylesheet -->
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Blocked Students</h1>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Block Reason</th>
                            <th>Blocked At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['block_reason']); ?></td>
                                <td><?php echo htmlspecialchars($row['blocked_at']); ?></td>
                                <td>
                                    <!-- Unblock Button -->
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to unblock this student?');" style="display: inline;">
                                        <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($row['student_id']); ?>">
                                        <button type="submit" name="unblock" class="btn btn-success btn-sm">Unblock</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center">No blocked students found.</p>
        <?php endif; ?>

        <?php
        // Handle unblock action
        if (isset($_POST['unblock']) && !empty($_POST['student_id'])) {
            $student_id = intval($_POST['student_id']);

            // Start transaction
            $conn->begin_transaction();

            try {
                // Set active = 1 in the students table
                $sql_unblock = "UPDATE students SET active = 1 WHERE id = ?";
                $stmt = $conn->prepare($sql_unblock);
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $stmt->close();

                // Remove the block reason from student_block_reasons table
                $sql_delete_reason = "DELETE FROM student_block_reasons WHERE student_id = ?";
                $stmt = $conn->prepare($sql_delete_reason);
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $stmt->close();

                // Commit transaction
                $conn->commit();

                echo "<script>
                        alert('Student has been unblocked successfully.');
                        window.location.href = 'blocked_students.php'; 
                      </script>";
            } catch (Exception $e) {
                $conn->rollback();
                echo "<p class='text-danger'>Error unblocking student: " . $e->getMessage() . "</p>";
            }
        }
        ?>

    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

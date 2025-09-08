<?php
// Include database connection
include('database_connection.php');

// Fetch pending admissions
$query = "SELECT * FROM Student_Admissions WHERE status = 'pending'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Admissions</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h2 {
            text-align: center;
            font-size: 1.8em;
            color: #333;
            margin-bottom: 30px;
        }

        /* Container and Card Styles */
        .card-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.2s ease;
        }
        .card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        /* Card Content */
        .card-content {
            flex: 1;
        }
        .card h3 {
            margin: 0;
            font-size: 1.5em;
            color: #333;
        }
        .card p {
            color: #666;
            margin: 5px 0;
            font-size: 0.9em;
        }

        /* Button Styling */
        .btn-container {
            display: flex;
            align-items: center;
        }
        .btn {
            text-decoration: none;
            color: #ffffff;
            background-color: #007acc;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            font-size: 0.9em;
            text-align: center;
        }
        .btn:hover {
            background-color: #005f99;
        }

        /* Responsive Styles */
        @media (max-width: 600px) {
            .card-container {
                width: 100%;
            }
            .card {
                flex-direction: column;
                align-items: flex-start;
            }
            .btn-container {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<h2>Pending Admissions</h2>

<div class="card-container">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card">
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($row['student_name']); ?></h3>
                    <p>Submitted Date: <?php echo htmlspecialchars($row['created_at']); ?></p>
                    <p>Assigning Grade: <?php echo htmlspecialchars($row['assigning_grade']); ?></p>
                </div>
                <div class="btn-container">
                    <a href="view_pending_admission_details.php?id=<?php echo $row['id']; ?>" class="btn">View Details</a>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No pending admissions found.</p>
    <?php endif; ?>
</div>

</body>
</html>

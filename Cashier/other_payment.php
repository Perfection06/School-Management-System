<?php
session_start();
include("database_connection.php");

// Check if the user is logged in and is an accountant
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Accountant') {
    // Redirect to login page if not authorized
    header("Location: login.php");
    exit;
}

$username = $_SESSION['user']['username'];
$role = $_SESSION['user']['role'];

// Process payment and generate receipt
if (isset($_POST['process_payment'])) {
    $student_username = $_POST['student_username'];
    $payment_name = $_POST['payment_name'];
    $payment_amount = $_POST['payment_amount'];
    $payment_date = date('Y-m-d H:i:s');

    // Get payment ID from the `other_payments` table
    $result = $conn->query("SELECT id FROM other_payments WHERE payment_name='$payment_name' AND payment_amount='$payment_amount'");
    $payment = $result->fetch_assoc();
    $payment_id = $payment['id'];

    // Insert into `student_payments` table (link student with payment)
    $conn->query("INSERT INTO student_payments (student_username, payment_id, status, payment_date) VALUES ('$student_username', '$payment_id', 'Paid', '$payment_date')");

    // Fetch student details for receipt
    $result = $conn->query("SELECT sa.student_name, sa.assigning_grade AS class, sa.father_mobile AS parent_phone, s.username 
                            FROM Student_Admissions sa 
                            JOIN Students s ON sa.id = s.id 
                            WHERE s.username='$student_username'");
    $student = $result->fetch_assoc();

    // Generate receipt
    echo "
    <div style='border: 1px solid #ddd; padding: 20px; width: 50%; margin: 20px auto;'>
        <h2 style='text-align: center;'>Payment Receipt</h2>
        <p><strong>Name:</strong> {$student['student_name']}</p>
        <p><strong>Student Username:</strong> {$student['username']}</p>
        <p><strong>Class:</strong> {$student['class']}</p>
        <p><strong>Parent Phone Number:</strong> {$student['parent_phone']}</p>
        <p><strong>Other Payment Name:</strong> $payment_name</p>
        <p><strong>Payment Date:</strong> $payment_date</p>
    </div>
    <button onclick='window.print()'>Print Receipt</button>
    ";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Payment Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .container {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            margin-top: 30px;
        }

        h2 {
            color: darkblue;
        }

        input,
        button {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
        }

        button {
            background-color: orange;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: maroon;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .pay-btn {
            background-color: green;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }

        .pay-btn:hover {
            background-color: darkgreen;
        }
    </style>
</head>
<?php include("navbar.php"); ?>

<body>
    <div class="container">
        <h2>Other Payment Registration</h2>
        <form method="POST">
            <label>Search by Student Username, Name, or Parent Phone:</label>
            <input type="text" name="search_input" required>
            <button type="submit" name="fetch_student_details" class="btn">Search</button>
        </form>

        <?php
        $student_details = null;

        if (isset($_POST['fetch_student_details'])) {
            $search_input = $_POST['search_input'];

            // Ensure valid input
            if (!empty($search_input)) {
                // Fetch student details by student name, username, or father's phone number
                $query = "
                    SELECT 
                        s.username AS student_username,
                        s.name AS student_name,
                        sa.father_mobile AS parent_phone,
                        g.grade_name AS class
                    FROM Students s
                    INNER JOIN Student_Admissions sa ON s.id = sa.id
                    INNER JOIN grades g ON sa.assigning_grade = g.id
                    WHERE s.name LIKE ? OR s.username LIKE ? OR sa.father_mobile LIKE ?
                    LIMIT 1
                ";

                $stmt = $conn->prepare($query);
                $stmt->bind_param("sss", $search_input, $search_input, $search_input);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $student_details = $result->fetch_assoc();
                } else {
                    echo "No student found with the provided details.";
                }

                $stmt->close();
            } else {
                echo "Please provide a valid search input.";
            }
        }

        if ($student_details) {
            echo "<h3>Student Details</h3>";
            echo "<p><strong>Name:</strong> {$student_details['student_name']}</p>";
            echo "<p><strong>Username:</strong> {$student_details['student_username']}</p>";
            echo "<p><strong>Class:</strong> {$student_details['class']}</p>";
            echo "<p><strong>Parent Phone:</strong> {$student_details['parent_phone']}</p>";
        }
        ?>

        <?php
        // Fetch payment details if student is found
        if ($student_details) {
            $query = "SELECT * FROM other_payments WHERE grade = ? ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $student_details['class']);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<h3>Outstanding Payments</h3>";
                echo "<table>
                <thead>
                    <tr>
                        <th>Payment Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>";
                while ($row = $result->fetch_assoc()) {
                    // Check if the payment is linked to the student
                    $payment_status_query = "SELECT status FROM student_payments WHERE student_username = ? AND payment_id = ?";
                    $stmt2 = $conn->prepare($payment_status_query);
                    $stmt2->bind_param("si", $student_details['student_username'], $row['id']);
                    $stmt2->execute();
                    $status_result = $stmt2->get_result();
                    $status = $status_result->fetch_assoc()['status'] ?? 'Pending';

                    echo "<tr>
                    <td>{$row['payment_name']}</td>
                    <td>{$row['payment_amount']}</td>
                    <td>$status</td>
                    <td>";
                    if ($status === 'Pending') {
                        echo "
                            <form method='POST'>
                                <input type='hidden' name='student_username' value='{$student_details['student_username']}'>
                                <input type='hidden' name='payment_name' value='{$row['payment_name']}'>
                                <input type='hidden' name='payment_amount' value='{$row['payment_amount']}' />
                                <button type='submit' name='process_payment' class='pay-btn'>Pay Now</button>
                            </form>";
                    } else {
                        echo "Paid";
                    }
                    echo "</td>
                  </tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<p>No outstanding payments for this student.</p>";
            }
            $stmt->close();
        }
        ?>
    </div>
</body>

</html>
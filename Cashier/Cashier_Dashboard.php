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



// Insert Fee Details
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["assign_payment"])) {
    $fee_per_month = $_POST["fee_per_month"];
    $discount_per_month = $_POST["discount_per_month"];

    // Insert fee details into assign_payment table
    $stmt = $conn->prepare("INSERT INTO assign_payment (fee_per_month, discount_per_month) VALUES (?, ?)");
    $stmt->bind_param("dd", $fee_per_month, $discount_per_month);
    $stmt->execute();
    $stmt->close();
}

// Fetch Student Details
$student_details = null;
$paid_months = [];
$unpaid_months = [];
$current_month_index = date("n") - 1;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["fetch_student_details"])) {
    $search_input = $_POST["search_input"];
    $father_phone = $_POST["father_phone"] ?? null;

    // Ensure valid input
    if (!empty($search_input) || !empty($father_phone)) {
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
            WHERE s.name = ? OR s.username = ? OR sa.father_mobile = ? 
            LIMIT 1
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $search_input, $search_input, $father_phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student_details = $result->fetch_assoc();

            // Fetch the months the student has already paid
            $paid_months_query = "SELECT months_paid FROM fee_registration WHERE student_username = ? ORDER BY payment_date DESC LIMIT 1";
            $stmt = $conn->prepare($paid_months_query);
            $stmt->bind_param("s", $student_details["student_username"]);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $paid_months = explode(',', $result->fetch_assoc()["months_paid"]);
            }
        } else {
            echo "No student found with the provided details.";
        }
        $stmt->close();

        // Calculate unpaid months
        $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        $unpaid_months = array_diff(array_slice($months, 0, $current_month_index + 1), $paid_months);
    } else {
        echo "Please provide a valid search input or father's phone number.";
    }
}

// Register Fee Payment
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["fee_registration"])) {
    $student_username = $_POST["student_username"];
    $student_name = $_POST["student_name"];
    $parent_phone = $_POST["parent_phone"];
    $class = $_POST["class"];
    $new_months_paid = $_POST["months_paid"]; // Selected months from the form
    $total_amount = $_POST["total_amount"];
    $payment_date = date("Y-m-d H:i:s");

    // Fetch the existing months paid for this student
    $stmt = $conn->prepare("SELECT months_paid FROM fee_registration WHERE student_username = ? ORDER BY payment_date DESC LIMIT 1");
    $stmt->bind_param("s", $student_username);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_months_paid = "";

    if ($result->num_rows > 0) {
        $existing_months_paid = $result->fetch_assoc()["months_paid"];
    }
    $stmt->close();

    // Merge existing months with new months and avoid duplicates
    $all_months_paid = array_unique(array_merge(explode(',', $existing_months_paid), $new_months_paid));

    // Create a string of months for the database
    $months_paid_string = implode(',', $all_months_paid);

    // Generate new receipt number
    $new_receipt_no = "R1000"; // Default starting value
    $last_receipt_query = $conn->query("SELECT receipt_no FROM fee_registration ORDER BY receipt_no DESC LIMIT 1");

    if ($last_receipt_query->num_rows > 0) {
        $last_receipt_no = $last_receipt_query->fetch_assoc()["receipt_no"];
        $last_number = intval(substr($last_receipt_no, 1)); // Remove "R" and get the number
        $new_receipt_no = "R" . ($last_number + 1); // Increment by 1
    }

    // Insert fee registration record with updated months_paid
    $stmt = $conn->prepare("INSERT INTO fee_registration (student_username, student_name, parent_phone, class, months_paid, total_amount, payment_date, receipt_no) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $student_username, $student_name, $parent_phone, $class, $months_paid_string, $total_amount, $payment_date, $new_receipt_no);
    $stmt->execute();
    $stmt->close();

    echo "Fee Payment has been successfully recorded and updated!";
}

// Generate Receipt
$receipt_details = null;
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["generate_receipt"])) {
    $student_username = $_POST["student_username"];

    // Ensure valid input
    if (!empty($student_username)) {
        // Fetch fee registration details for the searched student username
        $query = "
            SELECT 
                fr.student_username,
                fr.student_name,
                fr.parent_phone,
                fr.class,
                fr.months_paid,
                fr.total_amount,
                fr.discount_received,
                fr.final_amount,
                fr.payment_date,
                fr.receipt_no
            FROM fee_registration fr
            WHERE fr.student_username = ? 
            ORDER BY fr.payment_date DESC
            LIMIT 1
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $student_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $receipt_details = $result->fetch_assoc();
        } else {
            echo "No payment records found for the provided student username.";
        }
        $stmt->close();
    } else {
        echo "Please provide a valid student username.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
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

        label {
            font-weight: bold;
        }

        input,
        button {
            margin-bottom: 15px;
            width: 100%;
            padding: 8px;
        }

        .btn {
            background-color: orange;
            color: white;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: maroon;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .checkbox-group {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .checkbox-group td {
            text-align: center;
        }

        /* Profile icon styles */
        .profile-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
        }

        /* Print-only styles */
        @media print {
            body * {
                visibility: hidden;
            }

            #receipt-section,
            #receipt-section * {
                visibility: visible;
            }

            #receipt-section {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>
<?php include("navbar.php"); ?>
    <div class="container">
        <h2>Fee Registration</h2>
        <form method="POST">
            <label>Search Student (Username, Name, or Parent Phone):</label>
            <input type="text" name="search_input" required>
            <button type="submit" name="fetch_student_details" class="btn">Search</button>
        </form>

        <?php if ($student_details): ?>
            <form method="POST">
                <input type="hidden" name="student_username" value="<?= $student_details["student_username"] ?>">
                <label>Student Name:</label>
                <input type="text" name="student_name" value="<?= $student_details["student_name"] ?>" readonly>
                <label>Parent's Phone:</label>
                <input type="text" name="parent_phone" value="<?= $student_details["parent_phone"] ?>" readonly>
                <label>Class:</label>
                <input type="text" name="class" value="<?= $student_details["class"] ?>" readonly>

                <h3>Paid Months</h3>
                <ul>
                    <?php foreach ($paid_months as $month): ?>
                        <li><?= $month ?></li>
                    <?php endforeach; ?>
                </ul>

                <h3>Unpaid Months (up to the current month)</h3>
                <ul>
                    <?php foreach ($unpaid_months as $month): ?>
                        <li><?= $month ?></li>
                    <?php endforeach; ?>
                </ul>

                <label>Months to Pay:</label>
                <table>
                    <tr>
                        <?php foreach ($unpaid_months as $index => $month): ?>
                            <td>
                                <label>
                                    <input type="checkbox" name="months_paid[]" value="<?= $month ?>"> <?= $month ?>
                                </label>
                            </td>
                            <?php if (($index + 1) % 4 === 0) echo "</tr><tr>"; ?>
                        <?php endforeach; ?>
                    </tr>
                </table>

                <label>Total Amount Paid:</label>
                <input type="number" name="total_amount" required>
                <button type="submit" name="fee_registration" class="btn">Save</button>
            </form>
        <?php endif; ?>

        <h2>Generate Receipt</h2>
        <form method="POST">
            <label>Student Username:</label>
            <input type="text" name="student_username" required>
            <button type="submit" name="generate_receipt" class="btn">Generate</button>
        </form>

        <?php if ($receipt_details): ?>
            <div id="receipt-section">
                <h2>Receipt</h2>
                <table>
                    <tr>
                        <th>Receipt No</th>
                        <td><?= $receipt_details["receipt_no"] ?></td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td><?= $receipt_details["student_name"] ?></td>
                    </tr>
                    <tr>
                        <th>Student Username</th>
                        <td><?= $receipt_details["student_username"] ?></td>
                    </tr>
                    <tr>
                        <th>Class</th>
                        <td><?= $receipt_details["class"] ?></td>
                    </tr>
                    <tr>
                        <th>Parent Phone</th>
                        <td><?= $receipt_details["parent_phone"] ?></td>
                    </tr>
                    <tr>
                        <th>Amount Paid</th>
                        <td><?= $receipt_details["total_amount"] ?></td>
                    </tr>
                    <tr>
                        <th>Accepted Paid Months</th>
                        <td><?= $receipt_details["months_paid"] ?></td>
                    </tr>
                    <tr>
                        <th>Discount Received</th>
                        <td><?= $receipt_details["discount_received"] ?: 'No discount' ?></td>
                    </tr>
                    <tr>
                        <th>Final Amount</th>
                        <td><?= $receipt_details["final_amount"] ?></td>
                    </tr>
                    <tr>
                        <th>Payment Date</th>
                        <td><?= $receipt_details["payment_date"] ?></td>
                    </tr>
                </table>
                <button onclick="printReceipt()" class="btn">Print Receipt</button>
            </div>
        <?php endif; ?>

        <script>
            function printReceipt() {
                const originalContent = document.body.innerHTML;
                const receiptContent = document.getElementById('receipt-section').outerHTML;
                document.body.innerHTML = receiptContent;
                window.print();
                document.body.innerHTML = originalContent;
            }
        </script>

    </div>

</body>

</html>

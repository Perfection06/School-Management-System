<?php
include("database_connection.php");

// Initialize variables to avoid undefined errors
$student_details = null;
$paid_months = [];
$unpaid_months = [];
$receipt_details = null;
$all_months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
$current_month_index = date("n") - 1;

// Insert Fee Details
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["assign_payment"])) {
    $fee_per_month = $_POST["fee_per_month"];
    $discount_per_month = $_POST["discount_per_month"];
    $grade = $_POST["grade"];

    $stmt = $conn->prepare("INSERT INTO assign_payment (fee_per_month, discount_per_month, grade) VALUES (?, ?, ?)");
    $stmt->bind_param("dds", $fee_per_month, $discount_per_month, $grade);
    $stmt->execute();
    $stmt->close();
}

// Fetch Student Details
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["fetch_student_details"])) {
    $search_input = trim($_POST["search_input"]);

    if (!empty($search_input)) {
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
        $stmt->bind_param("sss", $search_input, $search_input, $search_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student_details = $result->fetch_assoc();

            // Fetch paid months
            $stmt = $conn->prepare("SELECT months_paid FROM fee_registration WHERE student_username = ? ORDER BY payment_date DESC LIMIT 1");
            $stmt->bind_param("s", $student_details["student_username"]);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $paid_months = explode(',', $result->fetch_assoc()["months_paid"]);
            }
        }
        $stmt->close();

        // Determine unpaid months (no leading commas)
        $unpaid_months = array_values(array_diff($all_months, $paid_months));
    }
}

// Register Fee Payment
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["fee_registration"])) {
    $student_username = $_POST["student_username"];
    $student_name = $_POST["student_name"];
    $parent_phone = $_POST["parent_phone"];
    $class = $_POST["class"];
    $new_months_paid = $_POST["months_paid"] ?? [];
    $payment_date = date("Y-m-d");

    // Fetch existing months paid
    $stmt = $conn->prepare("SELECT months_paid FROM fee_registration WHERE student_username = ? ORDER BY payment_date DESC LIMIT 1");
    $stmt->bind_param("s", $student_username);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_months_paid = "";

    if ($result->num_rows > 0) {
        $existing_months_paid = $result->fetch_assoc()["months_paid"];
    }
    $stmt->close();

    // Merge, remove empty values, and avoid leading/trailing commas
    $all_months_paid = array_unique(array_filter(array_merge(explode(',', $existing_months_paid), $new_months_paid)));
    $months_paid_string = implode(',', $all_months_paid);

    // Remove leading comma if exists
    $months_paid_string = ltrim($months_paid_string, ',');

    // Get Fee & Discount for the student's grade
    $stmt = $conn->prepare("SELECT fee_per_month, discount_per_month FROM assign_payment WHERE grade = ? LIMIT 1");
    $stmt->bind_param("s", $class);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "No fee details found for this grade!";
        exit;
    }
    
    $fee_details = $result->fetch_assoc();
    $fee_per_month = $fee_details["fee_per_month"];
    $discount_per_month = $fee_details["discount_per_month"];
    $stmt->close();

    // Calculate total fee and applicable discounts
    $num_months = count($new_months_paid);
    $total_fee = $num_months * $fee_per_month;
    $discount_received = 0;
    
    // Check if payment is made on or before 5th
    $current_day = date("j"); // Day of the month
    if ($current_day <= 5) {
        $discount_received = $num_months * $discount_per_month;
    }
    
    $final_amount = $total_fee - $discount_received;

    // Generate new receipt number
    $new_receipt_no = "R1000";
    $last_receipt_query = $conn->query("SELECT receipt_no FROM fee_registration ORDER BY id DESC LIMIT 1");

    if ($last_receipt_query->num_rows > 0) {
        $last_receipt_no = $last_receipt_query->fetch_assoc()["receipt_no"];
        $last_number = intval(substr($last_receipt_no, 1));
        $new_receipt_no = "R" . ($last_number + 1);
    }

    // Insert payment record
    $stmt = $conn->prepare("
        INSERT INTO fee_registration 
        (student_username, student_name, parent_phone, class, months_paid, total_amount, discount_received, final_amount, payment_date, receipt_no) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssssssss", 
        $student_username, 
        $student_name, 
        $parent_phone, 
        $class, 
        $months_paid_string, 
        $total_fee, 
        $discount_received, 
        $final_amount, 
        $payment_date, 
        $new_receipt_no
    );
    $stmt->execute();
    $stmt->close();

    echo "Fee Payment has been successfully recorded! Receipt No: " . $new_receipt_no;
}

// Generate Receipt
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["generate_receipt"])) {
    $student_username = $_POST["student_username"];

    // Fetch the latest fee payment record for the student
    $stmt = $conn->prepare("
        SELECT 
            receipt_no, 
            student_name, 
            student_username, 
            class, 
            parent_phone, 
            months_paid, 
            total_amount, 
            discount_received, 
            final_amount, 
            payment_date
        FROM fee_registration 
        WHERE student_username = ? 
        ORDER BY payment_date DESC 
        LIMIT 1
    ");
    $stmt->bind_param("s", $student_username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $receipt_details = $result->fetch_assoc();
    } else {
        echo "No payment found for this student.";
    }
    $stmt->close();
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
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: darkblue;
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        input,
        button {
            margin-bottom: 15px;
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .checkbox-group {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .checkbox-group td {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
        }

        td {
            font-size: 16px;
        }

        .receipt-container {
            border: 2px solid #4CAF50;
            padding: 30px;
            margin-top: 30px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .receipt-header {
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .receipt-details {
            width: 100%;
            border: none;
            margin-bottom: 30px;
        }

        .receipt-details td {
            padding: 8px;
        }

        .receipt-summary {
            background-color: #f9f9f9;
            padding: 10px;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }

        .receipt-summary td {
            font-size: 18px;
            font-weight: bold;
        }

        .print-btn {
            background-color: #000;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
            width: 100%;
        }

        .print-btn:hover {
            background-color: #333;
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

            .print-btn {
                display: none;
            }
        }
    </style>
</head>

<body>
<?php include('navbar.php'); ?>
    <div class="container">
        <h2>Fee Registration</h2>
        <form method="POST">
            <label>Search Student (Username, Name, or Parent Phone):</label>
            <input type="text" name="search_input" required>
            <button type="submit" name="fetch_student_details" class="btn">Search</button>
        </form>

        <?php if (!empty($student_details)): ?>
            <form method="POST">
                <label>Student Username:</label>
                <input type="text" name="student_username" value="<?= htmlspecialchars($student_details["student_username"]) ?>" readonly>
                <label>Student Name:</label>
                <input type="text" name="student_name" value="<?= htmlspecialchars($student_details["student_name"]) ?>" readonly>
                <label>Parent's Phone:</label>
                <input type="text" name="parent_phone" value="<?= htmlspecialchars($student_details["parent_phone"]) ?>" readonly>
                <label>Class:</label>
                <input type="text" name="class" value="<?= htmlspecialchars($student_details["class"]) ?>" readonly>

                <h3>Paid Months</h3>
                <ul>
                    <?php foreach ($paid_months as $month): ?>
                        <li><?= htmlspecialchars($month) ?></li>
                    <?php endforeach; ?>
                </ul>

                <h3>Unpaid Months (up to the current month)</h3>
                <table>
                    <tr>
                        <?php foreach ($unpaid_months as $index => $month): ?>
                            <td>
                                <label>
                                    <input type="checkbox" name="months_paid[]" value="<?= htmlspecialchars($month) ?>"> <?= htmlspecialchars($month) ?>
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

        <!-- Generate Receipt Form -->
        <h2>Generate Receipt</h2>
        <form method="POST">
            <label>Student Username:</label>
            <input type="text" name="student_username" required>
            <button type="submit" name="generate_receipt" class="btn">Generate</button>
        </form>

        <?php if (!empty($receipt_details)): ?>
            <div id="receipt-section" class="receipt-container">
                <div class="receipt-header">
                    <h2>Fee Payment Receipt</h2>
                </div>
                <table class="receipt-details">
                    <tr>
                        <td><strong>Receipt No:</strong></td>
                        <td><?= htmlspecialchars($receipt_details["receipt_no"]) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td><?= htmlspecialchars($receipt_details["student_name"]) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Student Username:</strong></td>
                        <td><?= htmlspecialchars($receipt_details["student_username"]) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Class:</strong></td>
                        <td><?= htmlspecialchars($receipt_details["class"]) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Parent Phone:</strong></td>
                        <td><?= htmlspecialchars($receipt_details["parent_phone"]) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Amount Paid:</strong></td>
                        <td><?= htmlspecialchars($receipt_details["total_amount"]) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Accepted Paid Months:</strong></td>
                        <td><?= htmlspecialchars($receipt_details["months_paid"]) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Discount Received:</strong></td>
                        <td><?= htmlspecialchars($receipt_details["discount_received"] ?: 'No discount') ?></td>
                    </tr>
                    <tr>
                        <td><strong>Final Amount:</strong></td>
                        <td><?= htmlspecialchars($receipt_details["final_amount"]) ?></td>
                    </tr>
                    <tr>
                        <td><strong>Payment Date:</strong></td>
                        <td><?= htmlspecialchars($receipt_details["payment_date"]) ?></td>
                    </tr>
                </table>

                <div class="receipt-summary">
                    <table>
                        <tr>
                            <td>Total Amount:</td>
                            <td><strong><?= htmlspecialchars($receipt_details["total_amount"]) ?> LKR</strong></td>
                        </tr>
                        <tr>
                            <td>Discount:</td>
                            <td><strong><?= htmlspecialchars($receipt_details["discount_received"]) ?> LKR</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Amount Due:</strong></td>
                            <td><strong><?= htmlspecialchars($receipt_details["final_amount"]) ?> LKR</strong></td>
                        </tr>
                    </table>
                </div>

                <button onclick="printReceipt()" class="print-btn">Print Receipt</button>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function printReceipt() {
            const receiptSection = document.getElementById('receipt-section');
            const printWindow = window.open('', '_blank', 'width=600,height=400');
            printWindow.document.write(receiptSection.innerHTML);
            printWindow.document.close();
            printWindow.print();
        }
    </script>

</body>

</html>

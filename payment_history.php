<?php
include("database_connection.php");

// Initialize variables
$search_query = "";
$filter = $_GET['filter'] ?? 'all';
$from_date = $_GET['from_date'] ?? "";
$payment_type = $_GET['payment_type'] ?? 'fee'; // Default to Fee Payments

// Determine the table and date column based on payment type
if ($payment_type === 'fee') {
    $table_name = 'fee_registration';
    $date_column = 'payment_date';
    $sql = "SELECT * FROM fee_registration WHERE 1=1";
} else {
    $table_name = 'other_payments';
    $date_column = 'created_at';
    $sql = "SELECT * FROM other_payments WHERE 1=1";
}

// Apply search functionality
if (!empty($_GET['search'])) {
    $search_query = $conn->real_escape_string($_GET['search']);
    if ($payment_type === 'fee') {
        $sql .= " AND (receipt_no LIKE '%$search_query%' 
                  OR student_username LIKE '%$search_query%' 
                  OR student_name LIKE '%$search_query%')";
    } else {
        $sql .= " AND (payment_name LIKE '%$search_query%')";
    }
}

// Apply filter by time period
switch ($filter) {
    case 'day':
        $sql .= " AND DATE($date_column) = CURDATE()";
        break;
    case 'week':
        $sql .= " AND WEEK($date_column) = WEEK(CURDATE())";
        break;
    case 'month':
        $sql .= " AND MONTH($date_column) = MONTH(CURDATE())";
        break;
    case 'year':
        $sql .= " AND YEAR($date_column) = YEAR(CURDATE())";
        break;
    case 'last_year':
        $sql .= " AND YEAR($date_column) = YEAR(CURDATE()) - 1";
        break;
}

// Apply filter for a specific date if provided
if (!empty($from_date)) {
    $sql = "SELECT * FROM $table_name WHERE DATE($date_column) = '$from_date' ORDER BY $date_column DESC";
} else {
    $sql .= " ORDER BY $date_column DESC";
}

// Fetch Payment History
$result = $conn->query($sql);

// Export filtered data to CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="payment_history.csv"');

    $output = fopen('php://output', 'w');
    if ($payment_type === 'fee') {
        fputcsv($output, ['Receipt No', 'Student Username', 'Student Name', 'Parent Phone', 'Class', 'Months Paid', 'Total Amount', 'Payment Date']);
    } else {
        fputcsv($output, ['Payment Name', 'Amount', 'Description', 'Created At', 'Grade']);
    }

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .container {
            max-width: auto;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h2 {
            color: darkblue;
            margin: 0;
        }

        .back-button {
            margin-bottom: 20px;
        }

        .back-button a {
            text-decoration: none;
            font-size: 16px;
            color: #4CAF50;
        }

        .back-button a:hover {
            color: #45a049;
        }

        .search-bar,
        .filter-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-bar input[type="text"],
        .filter-options select,
        .filter-options input[type="date"] {
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-bar button,
        .filter-options button {
            padding: 8px 16px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-bar button:hover,
        .filter-options button:hover {
            background-color: #45a049;
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

        th {
            background-color: #f4f4f4;
        }

        .year-header {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: left;
        }

        .month-header {
            background-color: #f7f7f7;
            text-align: left;
            font-style: italic;
        }
        .active-button {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            border: 2px solid #388E3C; /* Slightly darker border */
            padding: 10px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .active-button:hover {
            background-color: #388E3C; /* Darker green on hover */
        }
    </style>
    <script>
        function togglePayment(type) {
            window.location.href = 'payment_history.php?payment_type=' + type;
        }
    </script>

</head>

<body>
<?php include('navbar.php'); ?>
<div class="container">
    <!-- Back Button -->
    <div class="back-button">
        <a href="fee_management.php">&larr; Back</a>
    </div>

    <!-- Header -->
    <div class="header">
        <h2>Payment History</h2>
    </div>


    <!-- Search Bar -->
    <form class="search-bar" method="GET" action="payment_history.php">
        <input type="hidden" name="payment_type" value="<?= htmlspecialchars($payment_type) ?>">
        <input type="text" name="search" placeholder="Search" value="<?= htmlspecialchars($search_query) ?>">
        <button type="submit">Search</button>
    </form>

    <!-- Toggle Buttons -->
    <button onclick="togglePayment('fee')" class="<?= $payment_type === 'fee' ? 'active-button' : '' ?>">Fee Payments</button>
    <button onclick="togglePayment('other')" class="<?= $payment_type === 'other' ? 'active-button' : '' ?>">Other Payments</button>

    <!-- Filter Options -->
    <form class="filter-options" method="GET" action="payment_history.php">
        <input type="hidden" name="payment_type" value="<?= htmlspecialchars($payment_type) ?>">
        <select name="filter">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All Payments</option>
            <option value="day" <?= $filter === 'day' ? 'selected' : '' ?>>Today</option>
            <option value="week" <?= $filter === 'week' ? 'selected' : '' ?>>This Week</option>
            <option value="month" <?= $filter === 'month' ? 'selected' : '' ?>>This Month</option>
            <option value="year" <?= $filter === 'year' ? 'selected' : '' ?>>This Year</option>
            <option value="last_year" <?= $filter === 'last_year' ? 'selected' : '' ?>>Last Year</option>
        </select>
        <input type="date" name="from_date" value="<?= $_GET['from_date'] ?? '' ?>">
        <button type="submit">Filter</button>
        <button type="submit" name="export" value="1">Export to CSV</button>
        <button type="button" onclick="window.location.href='payment_history.php'">Reset</button>
    </form>

    <!-- Payment History Table -->
    <table>
        <thead>
            <tr>
                <?php if ($payment_type === 'fee'): ?>
                    <th>Receipt No</th>
                    <th>Student Username</th>
                    <th>Student Name</th>
                    <th>Parent Phone</th>
                    <th>Class</th>
                    <th>Months Paid</th>
                    <th>Total Amount</th>
                    <th>Payment Date</th>
                <?php else: ?>
                    <th>Payment Name</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Grade</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <?php if ($payment_type === 'fee'): ?>
                            <td><?= $row['receipt_no'] ?></td>
                            <td><?= $row['student_username'] ?></td>
                            <td><?= $row['student_name'] ?></td>
                            <td><?= $row['parent_phone'] ?></td>
                            <td><?= $row['class'] ?></td>
                            <td><?= $row['months_paid'] ?></td>
                            <td><?= $row['total_amount'] ?></td>
                            <td><?= $row['payment_date'] ?></td>
                        <?php else: ?>
                            <td><?= $row['payment_name'] ?></td>
                            <td><?= $row['payment_amount'] ?></td>
                            <td><?= $row['description'] ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td><?= $row['grade'] ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8">No payment history found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
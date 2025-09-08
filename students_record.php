<?php
include("database_connection.php");

// Sanitize GET parameters
$class_filter = isset($_GET['class_filter']) ? $conn->real_escape_string($_GET['class_filter']) : '';
$student_username_filter = isset($_GET['student_username_filter']) ? $conn->real_escape_string($_GET['student_username_filter']) : '';
$view_previous_year = isset($_GET['view_previous_year']) ? true : false;

// Determine current and previous years
$current_year = date("Y");
$previous_year = $current_year - 1;

// Build the query with filters
$query = "SELECT student_username, student_name, class, total_amount, months_paid FROM fee_registration";
$conditions = [];

// Filter by payment year
$conditions[] = "YEAR(payment_date) = " . ($view_previous_year ? $previous_year : $current_year);

// Apply class filter if provided
if (!empty($class_filter)) {
    $conditions[] = "class = '$class_filter'";
}

// Apply student username filter if provided
if (!empty($student_username_filter)) {
    $conditions[] = "student_username LIKE '%$student_username_filter%'";
}

// Combine conditions into the query
if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

// Order the results
$query .= " ORDER BY student_username";

// Execute the query
$result = $conn->query($query);
if (!$result) {
    die("Error fetching data: " . $conn->error);
}

// Fetch fee per month from the fee assignment table
$fee_data = $conn->query("SELECT fee_per_month FROM assign_payment LIMIT 1");
if (!$fee_data) {
    die("Error fetching fee data: " . $conn->error);
}
$fee_per_month = $fee_data->fetch_assoc()['fee_per_month'];

// Fetch "other payments" data
$other_payments_result = $conn->query("SELECT * FROM other_payments");
if (!$other_payments_result) {
    die("Error fetching other payments: " . $conn->error);
}
$other_payments = [];
while ($payment = $other_payments_result->fetch_assoc()) {
    $other_payments[] = $payment;
}

// Determine arrears cutoff month
$current_month = date("n"); // 1 for January, 2 for February, ..., 12 for December
$current_day = date("j"); // Day of the month
$arrears_cutoff_month = ($current_day >= 5) ? $current_month : $current_month - 1;

// Initialize summary data
$students_data = [];
$total_students = 0;
$total_payments_collected = 0;
$total_arrears = 0;
$students_with_unpaid_fees = 0;

while ($row = $result->fetch_assoc()) {
    $username = $row['student_username'];

    // Initialize data for a new student
    if (!isset($students_data[$username])) {
        $students_data[$username] = [
            'student_name' => $row['student_name'],
            'class' => $row['class'],
            'total_amount' => 0,
            'months_paid' => [],
            'other_payments' => []
        ];
        $total_students++;
    }

    // Aggregate data
    $students_data[$username]['total_amount'] += $row['total_amount'];
    $students_data[$username]['months_paid'] = array_merge($students_data[$username]['months_paid'], explode(',', $row['months_paid']));
}

// Calculate arrears and summary values
foreach ($students_data as $username => &$data) {
    $data['months_paid'] = array_unique($data['months_paid']);
    $months_paid_count = count($data['months_paid']);

    $arrears_months = $view_previous_year ? max(0, 12 - $months_paid_count) : max(0, $arrears_cutoff_month - $months_paid_count);
    $arrears = $arrears_months * $fee_per_month;

    $total_payments_collected += $data['total_amount'];
    if ($arrears > 0) {
        $total_arrears += $arrears;
        $students_with_unpaid_fees++;
    }
    $data['arrears'] = $arrears;
    $data['arrears_months'] = $arrears_months;

    // Fetch "other payments" for the student
    $other_payment_data = $conn->query("SELECT op.* FROM other_payments op
                                        JOIN student_payments sp ON sp.payment_id = op.id
                                        WHERE sp.student_username = '$username'");
    while ($payment = $other_payment_data->fetch_assoc()) {
        $data['other_payments'][] = $payment;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Records</title>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            const data = google.visualization.arrayToDataTable([
                ['Category', 'Amount'],
                ['Paid', <?= $total_payments_collected ?>],
                ['Arrears', <?= $total_arrears ?>],
            ]);

            const options = {
                title: 'Paid vs Arrears',
                pieHole: 0.4,
                colors: ['#4CAF50', '#FF5722']
            };

            const chart = new google.visualization.PieChart(document.getElementById('piechart'));
            chart.draw(data, options);
        }
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
        }

        h2 {
            color: darkblue;
        }

        label {
            font-weight: bold;
        }

        input,
        select,
        button {
            margin-bottom: 15px;
            padding: 8px;
        }

        .btn {
            background-color: orange;
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px 20px;
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
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }

        .summary-section {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }

        .summary-card {
            background-color: darkblue;
            color: white;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            flex: 1;
            margin: 0 10px;
        }

        #piechart {
            width: 100%;
            height: 400px;
        }

        .paid {
            background-color: #4CAF50;
        }

        .unpaid {
            background-color: #FF5722;
        }

        .irrelevant {
            background-color: #E0E0E0;
        }
    </style>
</head>

<body>
    <?php include('navbar.php'); ?>
    <div class="container">
        <h2>Student Records</h2>
        <!-- Filter Form -->
        <form method="GET" action="">
            <label for="class_filter">Class (Grade):</label>
            <input type="text" id="class_filter" name="class_filter" value="<?= htmlspecialchars($class_filter) ?>">

            <label for="student_username_filter">Student Username:</label>
            <input type="text" id="student_username_filter" name="student_username_filter" value="<?= htmlspecialchars($student_username_filter) ?>">

            <button type="submit" class="btn">Filter</button>
            <a href="students_record.php" class="btn">Reset</a>
        </form>
        <!-- Summary Section -->
        <div class="summary-section">
            <div class="summary-card">Total Students: <?= htmlspecialchars($total_students) ?></div>
            <div class="summary-card">Total Payments Collected: <?= htmlspecialchars($total_payments_collected) ?></div>
            <div class="summary-card">Total Arrears: <?= htmlspecialchars($total_arrears) ?></div>
            <div class="summary-card">Students with Unpaid Fees: <?= htmlspecialchars($students_with_unpaid_fees) ?></div>
        </div>
        <!-- Table and Chart -->
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Username</th>
                    <th>Class</th>
                    <th>Paid</th>
                    <th>Arrears</th>
                    <th>Months Paid</th>
                    <th>Other Payments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students_data as $username => $data): ?>
                    <tr class="<?= $data['arrears'] > 0 ? 'unpaid' : 'paid' ?>">
                        <td><?= htmlspecialchars($data['student_name']) ?></td>
                        <td><?= htmlspecialchars($username) ?></td>
                        <td><?= htmlspecialchars($data['class']) ?></td>
                        <td><?= htmlspecialchars($data['total_amount']) ?></td>
                        <td><?= htmlspecialchars($data['arrears']) ?></td>
                        <td><?= implode(', ', $data['months_paid']) ?></td>
                        <td>
                            <?php foreach ($data['other_payments'] as $payment): ?>
                                <?= htmlspecialchars($payment['description']) ?>: <?= htmlspecialchars($payment['payment_amount']) ?><br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div id="piechart"></div>
    </div>
</body>
</html>
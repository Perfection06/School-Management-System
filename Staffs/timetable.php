<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Staff') {
    header("Location: login.php");
    exit;
}


include ("database_connection.php");

// Initialize variables
$grade_id = isset($_GET['grade_id']) ? intval($_GET['grade_id']) : 0;
$errors = [];
$success = "";

// Days of the week
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

// Periods (1 to 10)
$periods = range(1, 10);

// Fetch grade details
$grade_name = "";
if ($grade_id > 0) {
    $grade_sql = "SELECT grade_name FROM grades WHERE id = ?";
    if ($stmt = $conn->prepare($grade_sql)) {
        $stmt->bind_param("i", $grade_id);
        $stmt->execute();
        $stmt->bind_result($grade_name);
        $stmt->fetch();
        $stmt->close();
    } else {
        $errors[] = "Failed to fetch grade details: " . $conn->error;
    }
} else {
    die("Invalid Grade ID.");
}

// Handle Form Submission
// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve timetable data
    $timetable = $_POST['timetable'] ?? [];

    // Basic validation: Ensure all subjects are filled except for the interval (Period 5)
    foreach ($days as $day) {
        foreach ($periods as $period) {
            // Skip validation for the interval period (Period 5 and Period 10)
            if ($period == 5 || $period == 10) {
                continue;
            }

            // Check if the key exists before accessing it
            if (isset($timetable[$day][$period]) && empty(trim($timetable[$day][$period]))) {
                $errors[] = "Subject for $day, Period $period is required.";
            }
        }
    }

    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert or update timetable entries
            foreach ($days as $day) {
                foreach ($periods as $period) {
                    $subject = trim($timetable[$day][$period] ?? ''); // Use null coalescing to avoid undefined index

                    // Check if the entry already exists
                    $check_sql = "SELECT id FROM timetables WHERE grade_id = ? AND day = ? AND period = ?";
                    $check_stmt = $conn->prepare($check_sql);
                    $check_stmt->bind_param("isi", $grade_id, $day, $period);
                    $check_stmt->execute();
                    $check_stmt->store_result();

                    if ($check_stmt->num_rows > 0) {
                        // Update existing entry
                        $check_stmt->bind_result($entry_id);
                        $check_stmt->fetch();
                        $update_sql = "UPDATE timetables SET subject = ? WHERE id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("si", $subject, $entry_id);
                        $update_stmt->execute();
                        $update_stmt->close();
                    } else {
                        // Insert new entry
                        $insert_sql = "INSERT INTO timetables (grade_id, day, period, subject) VALUES (?, ?, ?, ?)";
                        $insert_stmt = $conn->prepare($insert_sql);
                        $insert_stmt->bind_param("isis", $grade_id, $day, $period, $subject);
                        $insert_stmt->execute();
                        $insert_stmt->close();
                    }
                    $check_stmt->close();
                }
            }

            // Commit transaction
            $conn->commit();

            $success = "Time Table for $grade_name has been successfully saved.";

        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $errors[] = "Failed to save Time Table: " . $e->getMessage();
        }
    }
}


// Fetch existing timetable if available
$existing_timetable = [];
if ($grade_id > 0) {
    $fetch_tt_sql = "SELECT day, period, subject FROM timetables WHERE grade_id = ? ORDER BY FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'), period ASC";
    if ($stmt = $conn->prepare($fetch_tt_sql)) {
        $stmt->bind_param("i", $grade_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $existing_timetable[$row['day']][$row['period']] = $row['subject'];
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add/Edit Time Table - <?php echo htmlspecialchars($grade_name); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 20px;
        }
        .timetable-container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 1300px;
            margin: auto;
            position: relative;
        }
        .timetable-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .timetable-container form {
            width: 100%;
        }
        .timetable-container .form-group {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .timetable-container label {
            width: 150px;
            font-weight: bold;
            color: #555;
        }
        .timetable-container select {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .timetable-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .timetable-container th, .timetable-container td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .timetable-container th {
            background-color: #3498db;
            color: #fff;
        }
        .timetable-container .interval-row td {
            background-color: #f9e79f;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            height: 50px;
        }
        .timetable-container input[type="text"] {
            width: 100%;
            padding: 6px;
            box-sizing: border-box;
        }
        .timetable-container button {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #2ecc71;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        .timetable-container button:hover {
            background-color: #27ae60;
        }
        .print-button {
            position: absolute;
            top: 30px;
            right: 40px;
            padding: 10px 15px;
            background-color: #3498db;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .print-button:hover {
            background-color: #2980b9;
        }
        .error, .success {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .error {
            background-color: #e74c3c;
            color: #fff;
        }
        .success {
            background-color: #2ecc71;
            color: #fff;
        }
        .back-link {
            margin-top: 20px;
            text-align: center;
        }
        .back-link a {
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        /* Hide Print Button when printing */
        @media print {
    .print-button, .back-link, button[type="submit"] {
        display: none; /* Hide the print button and back link */
    }
    body {
        padding: 0; /* Remove padding */
    }
    .timetable-container {
        box-shadow: none; /* Remove box shadow */
        border: none; /* Remove border */
        padding: 0; /* Remove padding */
        margin: 0; /* Remove margin */
    }
    table {
        border-collapse: collapse; /* Ensure borders collapse */
    }
    th, td {
        border: 1px solid #000; /* Set border for table cells */
        padding: 8px; /* Padding for cells */
        text-align: center; /* Center align text */
    }
}

    </style>
    <script>
        function printTimeTable() {
            window.print();
        }
    </script>
</head>
<body>
    <div class="timetable-container">
        <h2>Time Table of <?php echo htmlspecialchars($grade_name); ?></h2>

        <!-- Print Button -->
        <button class="print-button" onclick="printTimeTable()">Print Time Table</button>

        <!-- Display Success Message -->
        <?php if (!empty($success)): ?>
            <div class="success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Display Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Time Table Form -->
        <form action="timetable.php?grade_id=<?php echo urlencode($grade_id); ?>" method="POST">
            <table>
                <tr>
                    <th>Period</th>
                    <?php foreach ($days as $day): ?>
                        <th><?php echo htmlspecialchars($day); ?></th>
                    <?php endforeach; ?>
                </tr>
                <?php for ($p = 1; $p <= 9; $p++): ?>
                    <?php if ($p == 5): ?>
                        <tr class="interval-row">
                            <td colspan="6">Interval</td>
                        </tr>
                        <?php continue; ?>
                    <?php endif; ?>
                    <tr>
                        <td><?php echo "Period $p"; ?></td>
                        <?php foreach ($days as $day): ?>
                            <td>
                                <input type="text" name="timetable[<?php echo htmlspecialchars($day); ?>][<?php echo $p; ?>]" 
                                value="<?php echo htmlspecialchars($existing_timetable[$day][$p] ?? ''); ?>" 
                                placeholder="Subject <?php echo ($p < 6) ? $p : ($p - 1); ?>">
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endfor; ?>
            </table>
            <button type="submit">Save Time Table</button>
        </form>

        <div class="back-link">
            <a href="add_timeTable.php">&larr; Back to Select Class</a>
        </div>
    </div>
</body>
</html>

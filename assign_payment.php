<?php
include("database_connection.php");

// Insert Fee Details for Grades
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["assign_payment"])) {
    $grade = $_POST["grade"];
    $fee_per_month = $_POST["fee_per_month"];
    $discount_per_month = $_POST["discount_per_month"];
    $conn->query("INSERT INTO assign_payment (grade, fee_per_month, discount_per_month) VALUES ('$grade', '$fee_per_month', '$discount_per_month')");
}

// Delete Fee Details for Grades
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $delete_id = $_POST["delete_id"];
    $conn->query("DELETE FROM assign_payment WHERE id = $delete_id");
}

// Insert Other Payments
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_other_payment"])) {
    $grade = $_POST["grade"];
    $payment_name = $_POST["payment_name"];
    $payment_amount = $_POST["payment_amount"];
    $description = $_POST["description"];

    if ($grade === "set_to_all") {
        // Insert for all grades dynamically from the 'grades' table
        $grades_result = $conn->query("SELECT grade_name FROM grades");
        if ($grades_result->num_rows > 0) {
            while ($grade_row = $grades_result->fetch_assoc()) {
                $grade_item = $grade_row['grade_name'];
                $conn->query("INSERT INTO other_payments (grade, payment_name, payment_amount, description) VALUES ('$grade_item', '$payment_name', '$payment_amount', '$description')");
            }
        }
    } else {
        // Insert for selected grade
        $conn->query("INSERT INTO other_payments (grade, payment_name, payment_amount, description) VALUES ('$grade', '$payment_name', '$payment_amount', '$description')");
    }
}

// Update Other Payments
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_other_payment"])) {
    $id = $_POST["id"];
    $grade = $_POST["grade"];
    $payment_name = $_POST["payment_name"];
    $payment_amount = $_POST["payment_amount"];
    $description = $_POST["description"];
    $conn->query("UPDATE other_payments SET grade = '$grade', payment_name = '$payment_name', payment_amount = '$payment_amount', description = '$description' WHERE id = $id");
}

// Delete Other Payments
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_other_payment"])) {
    $delete_id = $_POST["delete_id"];
    $conn->query("DELETE FROM other_payments WHERE id = $delete_id");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Payment</title>
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

        .delete-btn {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: darkred;
        }

        .edit-btn {
            background-color: blue;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }

        .edit-btn:hover {
            background-color: darkblue;
        }

        /* Print-only styles */
        @media print {
            body * {
                visibility: hidden;
            }

            .container,
            .container * {
                visibility: visible;
            }
        }
    </style>
</head>
<?php include("navbar.php"); ?>

<body>
    <div class="container">
        <h2>Assign Payment</h2>
        <form method="POST">
            <label>Grade:</label>
            <select name="grade" required>
                <?php
                $grades_result = $conn->query("SELECT grade_name FROM grades ORDER BY grade_name ASC");
                if ($grades_result->num_rows > 0) {
                    while ($grade_row = $grades_result->fetch_assoc()) {
                        echo "<option value='{$grade_row['grade_name']}'>{$grade_row['grade_name']}</option>";
                    }
                }
                ?>
                <option value="set_to_all">Set to All</option>
            </select>
            <label>Fee Per Month:</label>
            <input type="number" name="fee_per_month" required>
            <label>Discount Per Month:</label>
            <input type="number" name="discount_per_month" required>
            <button type="submit" name="assign_payment" class="btn">Save</button>
        </form>

        <h2>Existing Grade-Based Fee Details</h2>
        <table>
            <thead>
                <tr>
                    <th>Grade</th>
                    <th>Fee Per Month</th>
                    <th>Discount Per Month</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM assign_payment ORDER BY grade ASC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['grade']}</td>
                                <td>{$row['fee_per_month']}</td>
                                <td>{$row['discount_per_month']}</td>
                                <td>
                                    <form method='POST' style='display:inline;'>
                                        <input type='hidden' name='delete_id' value='{$row['id']}'>
                                        <button type='submit' class='delete-btn'>Delete</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <h2>Other Payments</h2>
        <form method="POST">
            <label>Grade:</label>
            <select name="grade" required>
                <?php
                // Grades Dropdown (Fixing the code here)
                $grades_result = $conn->query("SELECT grade_name FROM grades ORDER BY grade_name ASC");
                if ($grades_result && $grades_result->num_rows > 0) {
                    while ($grade_row = $grades_result->fetch_assoc()) {
                        echo "<option value='{$grade_row['grade_name']}'>{$grade_row['grade_name']}</option>";
                    }
                }

                ?>
                <option value="set_to_all">Set to All</option>
            </select>
            <label>Payment Name:</label>
            <input type="text" name="payment_name" required>
            <label>Payment Amount:</label>
            <input type="number" step="0.01" name="payment_amount" required>
            <label>Description:</label>
            <input type="text" name="description">
            <button type="submit" name="add_other_payment" class="btn">Add Payment</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Grade</th>
                    <th>Payment Name</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $other_payments = $conn->query("SELECT * FROM other_payments ORDER BY grade ASC");
                if ($other_payments->num_rows > 0) {
                    while ($payment = $other_payments->fetch_assoc()) {
                        echo "<tr>
                        <td>{$payment['grade']}</td>
                        <td>{$payment['payment_name']}</td>
                        <td>{$payment['payment_amount']}</td>
                        <td>{$payment['description']}</td>
                        <td>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='id' value='{$payment['id']}'>
                                <select name='grade'>";
                        // Fetch and display grades dynamically
                        $grades_result = $conn->query("SELECT grade_name FROM grades ORDER BY grade_name ASC");
                        if ($grades_result->num_rows > 0) {
                            while ($grade_row = $grades_result->fetch_assoc()) {
                                $selected = ($payment['grade'] === $grade_row['grade_name']) ? "selected" : "";
                                echo "<option value='{$grade_row['grade_name']}' {$selected}>{$grade_row['grade_name']}</option>";
                            }
                        }
                        echo "</select>
                                <input type='text' name='payment_name' value='{$payment['payment_name']}' required>
                                <input type='number' step='0.01' name='payment_amount' value='{$payment['payment_amount']}' required>
                                <input type='text' name='description' value='{$payment['description']}'>
                                <button type='submit' name='update_other_payment' class='edit-btn'>Update</button>
                            </form>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='delete_id' value='{$payment['id']}'>
                                <button type='submit' name='delete_other_payment' class='delete-btn'>Delete</button>
                            </form>
                        </td>
                      </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>
</body>


</html>
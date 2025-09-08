<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include("database_connection.php");

// Initialize NIC and Student Name search variables
$nic_search = '';
$student_name_search = '';

// Check if the form is submitted with a search term
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Only access the $_GET variables if they are set
    $nic_search = isset($_GET['search_nic']) ? $_GET['search_nic'] : '';
    $student_name_search = isset($_GET['search_student_name']) ? $_GET['search_student_name'] : '';
}

// Modify SQL query to filter results based on NIC number or Student Name if search terms are provided
$sql = "SELECT * FROM pre_addmission_details";
$conditions = [];
$params = [];

// Add condition for NIC search if provided
if (!empty($nic_search)) {
    $conditions[] = "nic_number LIKE ?";
    $params[] = "%" . $nic_search . "%";
}

// Add condition for Student Name search if provided
if (!empty($student_name_search)) {
    $conditions[] = "student_name LIKE ?";
    $params[] = "%" . $student_name_search . "%";
}

// If there are any search conditions, append them to the query
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" OR ", $conditions);
}

$sql .= " ORDER BY submission_date DESC";

$stmt = $conn->prepare($sql);

if (count($params) > 0) {
    // Bind the parameters based on the search terms
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporary Students</title>
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
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button.delete {
            background-color: #e74c3c;
        }
        button.print {
            background-color: #3498db;
        }
        button:hover {
            background-color: #45a049;
        }
        button.delete:hover {
            background-color: #c0392b;
        }
        button.print:hover {
            background-color: #2980b9;
        }
        form {
            margin: 0;
        }
        .search-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-container input[type="text"] {
            padding: 8px;
            font-size: 16px;
            width: 200px;
            margin-right: 10px;
        }
        .search-container button {
            padding: 8px 12px;
            font-size: 16px;
        }
        @media screen and (max-width: 768px) {
            table {
                font-size: 14px;
            }
            th, td {
                padding: 10px;
            }
            button {
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

    <h2>Temporary Students</h2>

    <!-- Search Form -->
    <div class="search-container">
        <form method="GET" action="view_temporary_details.php">
            <input type="text" name="search_nic" value="<?php echo htmlspecialchars($nic_search); ?>" placeholder="Search by NIC number">
            <input type="text" name="search_student_name" value="<?php echo htmlspecialchars($student_name_search); ?>" placeholder="Search by Student Name">
            <button type="submit">Search</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Parent Name</th>
                <th>Relationship</th>
                <th>Student Name</th>
                <th>NIC</th>
                <th>Address</th>
                <th>Contact Number</th>
                <th>Email</th>
                <th>Requested Class</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr id='row-" . htmlspecialchars($row['id']) . "'>";
                    echo "<td>" . htmlspecialchars($row['parent_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['relationship']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nic_number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>"; 
                    echo "<td>" . htmlspecialchars($row['requested_class']) . "</td>";
                    echo '<td>
                            <a href="send_admission_email.php?email=' . urlencode($row['email']) . '" style="text-decoration: none;">
                                <button type="button">Send Admission</button>
                            </a>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="temp_student_id" value="' . htmlspecialchars($row['id']) . '">
                                <button type="submit" name="delete" class="delete">Delete</button>
                            </form>
                            <button class="print" onclick="printRow(' . htmlspecialchars($row['id']) . ')">Print</button>
                        </td>';
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No temporary students found</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <script>
        function printRow(rowId) {
            var row = document.getElementById('row-' + rowId);
            var printWindow = window.open('', '', 'height=600, width=800');
            printWindow.document.write('<html><head><title>Print Student Details</title></head><body>');
            printWindow.document.write('<h3>Student Details</h3>');
            
            // Collect and display the row data in a readable format
            var rowData = row.getElementsByTagName('td');
            printWindow.document.write('<p><strong>Parent Name:</strong> ' + rowData[0].innerText + '</p>');
            printWindow.document.write('<p><strong>Relationship:</strong> ' + rowData[1].innerText + '</p>');
            printWindow.document.write('<p><strong>Student Name:</strong> ' + rowData[2].innerText + '</p>');
            printWindow.document.write('<p><strong>NIC:</strong> ' + rowData[3].innerText + '</p>');
            printWindow.document.write('<p><strong>Address:</strong> ' + rowData[4].innerText + '</p>');
            printWindow.document.write('<p><strong>Contact Number:</strong> ' + rowData[5].innerText + '</p>');
            printWindow.document.write('<p><strong>Email:</strong> ' + rowData[6].innerText + '</p>');
            printWindow.document.write('<p><strong>Requested Class:</strong> ' + rowData[7].innerText + '</p>');
            
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.print();
        }
    </script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

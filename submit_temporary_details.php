<?php
session_start();

include("database_connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form input
    $parent_name = $_POST['parent_name'];
    $relationship = $_POST['relationship'];
    $student_name = $_POST['student_name'];
    $dob = $_POST['dob'];
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];
    $requested_class = $_POST['requested_class'];
    $email = $_POST['email'];
    $nic_number = $_POST['nic_number']; 

    // Check if NIC number is empty
    if (empty($nic_number)) {
        echo "<script>
                alert('NIC number cannot be empty!');
                window.location.href = 'submit_temporary_details.php'; // Redirect to the form page
              </script>";
        exit;
    }

    // Check if NIC number already exists in the database
    $check_sql = "SELECT * FROM pre_addmission_details WHERE nic_number = '$nic_number'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        echo "<script>
                alert('NIC number already exists!');
                window.location.href = 'submit_temporary_details.php'; // Redirect to the form page
              </script>";
        exit;
    }

    // Insert the temporary student details into the database
    $sql = "INSERT INTO pre_addmission_details (parent_name, relationship, student_name, dob, address, contact_number, requested_class, email, nic_number)
        VALUES ('$parent_name', '$relationship', '$student_name', '$dob', '$address', '$contact_number', '$requested_class', '$email', '$nic_number')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Temporary details submitted successfully!');
                window.location.href = 'view_temporary_details.php'; // Redirect to the view page
              </script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Temporary Student Details</title>
    <style>
        /* General page styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Form container styling */
        form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        /* Form header styling */
        h2 {
            text-align: center;
            color: #333333;
            margin-bottom: 20px;
        }

        /* Input fields and labels styling */
        label {
            display: block;
            color: #333333;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size: 16px;
        }

        /* Textarea styling */
        textarea {
            height: 80px;
            resize: vertical;
        }

        /* Submit button styling */
        button[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        /* Hover effect for the button */
        button[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Responsive design */
        @media (max-width: 600px) {
            form {
                padding: 15px;
            }
            
            input[type="text"],
            input[type="date"],
            select,
            textarea {
                font-size: 14px;
            }
            
            button[type="submit"] {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<form method="POST" action="submit_temporary_details.php">
    <h2>Temporary Student Details Form</h2>

    <label for="parent_name">Parent/Guardian Name:</label>
    <input type="text" name="parent_name" required>

    <label for="relationship">Relationship to Student:</label>
    <select name="relationship" required>
        <option value="Mother">Mother</option>
        <option value="Father">Father</option>
        <option value="Guardian">Guardian</option>
    </select>

    <label for="student_name">Student Name:</label>
    <input type="text" name="student_name" required>

    <label for="dob">Date of Birth:</label>
    <input type="date" name="dob" required>

    <label for="address">Address:</label>
    <textarea name="address" required></textarea>

    <label for="contact_number">Contact Number:</label>
    <input type="text" name="contact_number" required>

    <label for="email">Email Address:</label>
    <input type="email" name="email" required>

    <label for="requested_class">Requested Class:</label>
    <input type="text" name="requested_class" required>


    <label for="nic_number">NIC Number:</label>
    <input type="text" name="nic_number" required>

    <button type="submit">Submit</button>
</form>

</body>
</html>

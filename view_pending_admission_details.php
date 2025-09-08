<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
include('database_connection.php');

// Check if ID is set
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Sanitize input

    // Fetch admission details
    $query = "SELECT * FROM Student_Admissions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        die("Admission not found.");
    }
} else {
    die("Invalid ID.");
}

// Fetch grades for dropdown
$grades_query = "SELECT * FROM grades"; // Adjust table name as necessary
$grades_result = $conn->query($grades_query);

// Fetch sibling details based on student admission ID
$sibling_query = "SELECT * FROM siblings WHERE student_admission_id = ?";
$sibling_stmt = $conn->prepare($sibling_query);
$sibling_stmt->bind_param("i", $id);
$sibling_stmt->execute();
$sibling_result = $sibling_stmt->get_result();

// Fetch all siblings
$siblings = [];
while ($sibling = $sibling_result->fetch_assoc()) {
    $siblings[] = $sibling;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Pending Admission Details</title>
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f9fbfc;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h2 {
            text-align: center;
            font-size: 2.2em;
            color: #005eaa;
            margin-bottom: 25px;
            font-weight: 600;
        }
        
        /* Container Styles */
        .details-container, .form-container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 30px;
            transition: transform 0.2s ease;
        }
        .details-container:hover, .form-container:hover {
            transform: scale(1.02);
        }

        h3 {
            font-size: 1.8em;
            color: #007acc;
            margin-top: 0;
            margin-bottom: 18px;
            border-bottom: 3px solid #007acc;
            padding-bottom: 8px;
            font-weight: 600;
        }
        
        /* Profile Image Styles */
        .student-image {
            text-align: center;
            margin-bottom: 20px;
        }
        .student-image img {
            border-radius: 50%;
            border: 4px solid #007acc;
            width: 200px;
            height: 200px;
        }

        /* Detail Section Styles */
        .details-container p {
            margin: 8px 0;
            line-height: 1.6;
            font-size: 1em;
        }
        .details-container strong {
            color: #005eaa;
        }

        /* Form Styles */
        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        label {
            font-weight: bold;
            color: #555;
            font-size: 1.1em;
        }
        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccd4db;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box;
        }
        button {
            background-color: #007acc;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
            transition: background-color 0.2s ease;
        }
        button:hover {
            background-color: #005f99;
        }
        
        /* Reject Button Styles */
        .reject-form button {
            background-color: #e53e3e;
            font-size: 1.1em;
        }
        .reject-form button:hover {
            background-color: #c53030;
        }

        .sig{
            height: 100px;
            padding-left: 10px;
        }
        
        /* Responsive Styles */
        @media (max-width: 600px) {
            .details-container, .form-container {
                padding: 20px;
            }
            .student-image img {
                width: 150px;
                height: 150px;
            }
            h2 {
                font-size: 1.8em;
            }
            h3 {
                font-size: 1.4em;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<h2>Admission Details of <?php echo htmlspecialchars($row['student_name']); ?></h2>

<div class="student-image">
    <?php if (!empty($row['student_image']) && file_exists($row['student_image'])): ?>
        <img src="<?php echo htmlspecialchars($row['student_image']); ?>" alt="Student Image">
    <?php else: ?>
        <img src="./Resources/default_profile.png" alt="No Image">
    <?php endif; ?>
</div>



<!-- Display all admission details -->
<div class="details-container">
    <h3>Student Details</h3>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($row['student_name']); ?></p>
    <p><strong>Address:</strong> <?php echo htmlspecialchars($row['student_address']); ?></p>
    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($row['student_dob']); ?></p>
    <p><strong>Gender:</strong> <?php echo htmlspecialchars($row['student_gender']); ?></p>
    <p><strong>Nationality:</strong> <?php echo htmlspecialchars($row['student_nationality']); ?></p>
    <p><strong>Religion:</strong> <?php echo htmlspecialchars($row['student_religion']); ?></p>
    <p><strong>Mother Tongue:</strong> <?php echo htmlspecialchars($row['student_mother_tongue']); ?></p>
    <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($row['student_phone']); ?></p>
    <p><strong>Assigning Grade:</strong> <?php echo htmlspecialchars($row['assigning_grade']); ?></p>
    <p><strong>Previous School Attended:</strong> <?php echo htmlspecialchars($row['school_attended']); ?></p>
    <p><strong>School Address:</strong> <?php echo htmlspecialchars($row['school_address']); ?></p>
    <p><strong>School Medium:</strong> <?php echo htmlspecialchars($row['school_medium']); ?></p>
    <p><strong>Second Language:</strong> <?php echo htmlspecialchars($row['second_language']); ?></p>
    <p><strong>Grade Passed:</strong> <?php echo htmlspecialchars($row['grade_passed']); ?></p>
    <p><strong>Last Attended Date:</strong> <?php echo htmlspecialchars($row['last_attended']); ?></p>
    <p><strong>Duration of Stay:</strong> <?php echo htmlspecialchars($row['duration_of_stay']); ?></p>
    <p><strong>Reason for Leaving:</strong> <?php echo htmlspecialchars($row['reason_for_leaving']); ?></p>
    <p><strong>Special Attention:</strong> <?php echo htmlspecialchars($row['special_attention']); ?></p>

    <h3>Father's Details</h3>
    <p><strong>Father's Name:</strong> <?php echo htmlspecialchars($row['father_name']); ?></p>
    <p><strong>Father's ID:</strong> <?php echo htmlspecialchars($row['father_id']); ?></p>
    <p><strong>Father's Date of Birth:</strong> <?php echo htmlspecialchars($row['father_dob']); ?></p>
    <p><strong>Father's Occupation:</strong> <?php echo htmlspecialchars($row['father_occupation']); ?></p>
    <p><strong>Father's School:</strong> <?php echo htmlspecialchars($row['father_school']); ?></p>
    <p><strong>Father's Education:</strong> <?php echo htmlspecialchars($row['father_education']); ?></p>
    <p><strong>Father's Mobile Number:</strong> <?php echo htmlspecialchars($row['father_mobile']); ?></p>
    <p><strong>Father's Residence:</strong> <?php echo htmlspecialchars($row['father_residence']); ?></p>
    <p><strong>Father's Email:</strong> <?php echo htmlspecialchars($row['father_email']); ?></p>

    <h3>Mother's Details</h3>
    <p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($row['mother_name']); ?></p>
    <p><strong>Mother's ID:</strong> <?php echo htmlspecialchars($row['mother_id']); ?></p>
    <p><strong>Mother's Date of Birth:</strong> <?php echo htmlspecialchars($row['mother_dob']); ?></p>
    <p><strong>Mother's Occupation:</strong> <?php echo htmlspecialchars($row['mother_occupation']); ?></p>
    <p><strong>Mother's School:</strong> <?php echo htmlspecialchars($row['mother_school']); ?></p>
    <p><strong>Mother's Education:</strong> <?php echo htmlspecialchars($row['mother_education']); ?></p>
    <p><strong>Mother's Mobile Number:</strong> <?php echo htmlspecialchars($row['mother_mobile']); ?></p>
    <p><strong>Mother's Residence:</strong> <?php echo htmlspecialchars($row['mother_residence']); ?></p>
    <p><strong>Mother's Email:</strong> <?php echo htmlspecialchars($row['mother_email']); ?></p>

    <h3>Siblings</h3>

<?php if (count($siblings) > 0): ?>
    <?php foreach ($siblings as $sibling): ?>
        <p>
            <strong>Name:</strong> <?php echo htmlspecialchars($sibling['name'] ?? 'N/A'); ?><br>
            <strong>Gender:</strong> <?php echo htmlspecialchars($sibling['gender'] ?? 'N/A'); ?><br>
            <strong>Date of Birth:</strong> <?php echo htmlspecialchars($sibling['dob'] ?? 'N/A'); ?><br>
            <strong>School:</strong> <?php echo htmlspecialchars($sibling['school'] ?? 'N/A'); ?><br>
            <strong>Grade:</strong> <?php echo htmlspecialchars($sibling['grade'] ?? 'N/A'); ?>
        </p>
    <?php endforeach; ?>
<?php else: ?>
    <p>No siblings found.</p>
<?php endif; ?>




    <h3>Guardian's Details</h3>
    <p><strong>Guardian's Name:</strong> <?php echo htmlspecialchars($row['guardian_name']); ?></p>
    <p><strong>Guardian's ID:</strong> <?php echo htmlspecialchars($row['guardian_id']); ?></p>
    <p><strong>Guardian's Date of Birth:</strong> <?php echo htmlspecialchars($row['guardian_dob']); ?></p>
    <p><strong>Guardian's Occupation:</strong> <?php echo htmlspecialchars($row['guardian_occupation']); ?></p>
    <p><strong>Guardian's School:</strong> <?php echo htmlspecialchars($row['guardian_school']); ?></p>
    <p><strong>Guardian's Education:</strong> <?php echo htmlspecialchars($row['guardian_education']); ?></p>
    <p><strong>Guardian's Mobile Number:</strong> <?php echo htmlspecialchars($row['guardian_mobile']); ?></p>
    <p><strong>Guardian's Residence:</strong> <?php echo htmlspecialchars($row['guardian_residence']); ?></p>
    <p><strong>Guardian's Email:</strong> <?php echo htmlspecialchars($row['guardian_email']); ?></p>
    <p><strong>Guardian's Relationship:</strong> <?php echo htmlspecialchars($row['guardian_relationship']); ?></p>
    <p><strong>Reason for Acting as Guardian:</strong> <?php echo htmlspecialchars($row['guardian_reason']); ?></p>

    <h3>Emergency Contact</h3>
    <p><strong>Emergency Contact Name:</strong> <?php echo htmlspecialchars($row['emergency_name']); ?></p>
    <p><strong>Relationship to Student:</strong> <?php echo htmlspecialchars($row['emergency_relationship']); ?></p>
    <p><strong>Emergency Mobile Number:</strong> <?php echo htmlspecialchars($row['emergency_mobile']); ?></p>
    <p><strong>Emergency Residence:</strong> <?php echo htmlspecialchars($row['emergency_residence']); ?></p>
    <p><strong>Emergency Office:</strong> <?php echo htmlspecialchars($row['emergency_office']); ?></p>
    <p><strong>Emergency Fax:</strong> <?php echo htmlspecialchars($row['emergency_fax']); ?></p>

    <h3>Additional Details</h3>
    <p><strong>Parents Together:</strong> <?php echo htmlspecialchars($row['parents_together']); ?></p>
    <p><strong>Parents Reason (if separated):</strong> <?php echo htmlspecialchars($row['parents_reason']); ?></p>
    <p><strong>Remarks:</strong> <?php echo htmlspecialchars($row['remarks']); ?></p>

    <h3>Signature</h3>
    <p><strong>Signature:</strong></p>
    <img src="<?php echo htmlspecialchars($row['signature_image']); ?>" alt="" class="sig">
</div>

<!-- Assign Grade Form -->
<div class="form-container">
    <h3>Assign Grade</h3>
    <form action="assign_grade.php" method="post">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        
        <label for="assigning_grade">Assign Grade:</label>
        <select name="assigning_grade" required>
            <option value="">Select Grade</option>
            <?php while ($grade = $grades_result->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($grade['id']); ?>">
                    <?php echo htmlspecialchars($grade['grade_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>


        <label for="username">Username:</label>
        <input type="text" name="username" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Assign</button>
    </form>
</div>

<!-- Reject Button -->
<div class="form-container reject-form">
    <form action="reject_admission.php" method="post">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <button type="submit">Reject Admission</button>
    </form>
</div>

</body>
</html>

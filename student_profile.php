<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}


// Check if student is logged in and fetch student ID from session
if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']); // Use $_GET['id'] to fetch student ID

    // Include database connection
    include('database_connection.php');

    // SQL query to fetch student details along with grade name and admission details
    $query = "
        SELECT 
            s.id AS student_id,
            s.name AS student_name,
            s.username,
            s.active,
            g.grade_name,
            sa.student_address,
            sa.student_dob,
            sa.student_gender,
            sa.student_nationality,
            sa.student_phone,
            sa.student_religion,
            sa.student_mother_tongue,
            sa.student_image,
            sa.school_attended,
            sa.school_address,
            sa.school_medium,
            sa.second_language,
            sa.grade_passed,
            sa.last_attended,
            sa.duration_of_stay,
            sa.reason_for_leaving,
            sa.special_attention,
            sa.father_name,
            sa.father_dob,
            sa.father_occupation,
            sa.father_mobile,
            sa.father_email,
            sa.father_school,
            sa.father_education,
            sa.father_residence,
            sa.mother_name,
            sa.mother_dob,
            sa.mother_occupation,
            sa.mother_mobile,
            sa.mother_email,
            sa.mother_school,
            sa.mother_education,
            sa.mother_residence,
            sa.guardian_name,
            sa.guardian_dob,
            sa.guardian_occupation,
            sa.guardian_mobile,
            sa.guardian_email,
            sa.guardian_relationship,
            sa.guardian_reason,
            sa.guardian_school,
            sa.guardian_education,
            sa.guardian_residence,
            sa.parents_together,
            sa.parents_reason,
            sa.remarks,
            sa.emergency_name AS emergency_contact_name,
            sa.emergency_relationship,
            sa.emergency_mobile,
            sa.emergency_residence,
            sa.emergency_office,
            sa.emergency_fax
        FROM Students s
        LEFT JOIN grades g ON s.grade_id = g.id
        LEFT JOIN Student_Admissions sa ON s.id = sa.id
        WHERE s.id = ?
    ";

    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo "Error preparing statement: " . htmlspecialchars($conn->error);
        exit;
    }

    $stmt->bind_param("i", $student_id);
    if (!$stmt->execute()) {
        echo "Error executing statement: " . htmlspecialchars($stmt->error);
        exit;
    }

    $result = $stmt->get_result();

    // Check if student found
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        echo "Student not found.";
        exit;
    }
} else {
    echo "No student ID provided.";
    exit;
}

// Fetch sibling details based on student admission ID
$sibling_query = "SELECT * FROM siblings WHERE student_admission_id = ?";
$sibling_stmt = $conn->prepare($sibling_query);
$sibling_stmt->bind_param("i", $student_id); // Use $student_id instead of $id
$sibling_stmt->execute();
$sibling_result = $sibling_stmt->get_result();

// Fetch all siblings
$siblings = [];
while ($sibling = $sibling_result->fetch_assoc()) {
    $siblings[] = $sibling;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    // Sanitize and fetch student ID from the form
    $student_id = intval($_POST['id']);

    // Check if student exists in Student_Admissions table
    $check_query = "SELECT * FROM Student_Admissions WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // If student exists, proceed to delete
        $delete_query = "DELETE FROM Student_Admissions WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);

        if ($delete_stmt) {
            $delete_stmt->bind_param("i", $student_id);

            if ($delete_stmt->execute()) {
                // Deletion successful
                echo "<script>alert('Student successfully deleted.'); window.location.href = 'view_students.php';</script>";
            } else {
                // Deletion failed
                echo "Error deleting record: " . htmlspecialchars($delete_stmt->error);
            }
        } else {
            echo "Error preparing delete statement: " . htmlspecialchars($conn->error);
        }
    } else {
        // Student not found
        echo "<script>alert('Student not found.'); window.location.href = 'students_list.php';</script>";
    }
} 

// Handle blocking a student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block'])) {
    if (!empty($_POST['student_id']) && !empty($_POST['block_reason'])) {
        $student_id = intval($_POST['student_id']);
        $block_reason = $_POST['block_reason'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update student active status
            $sql_block = "UPDATE students SET active = 0 WHERE id = ?";
            $stmt = $conn->prepare($sql_block);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $stmt->close();

            // Insert block reason
            $sql_reason = "INSERT INTO student_block_reasons (student_id, block_reason) VALUES (?, ?)";
            $stmt = $conn->prepare($sql_reason);
            $stmt->bind_param("is", $student_id, $block_reason);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            echo "<script>
                    alert('Student has been blocked successfully.');
                    window.location.href = 'view_students.php'; // Redirect to student list
                  </script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p>Error blocking student: " . $e->getMessage() . "</p>";
        }
    }
}

// Handle unblocking a student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate'])) {
    if (!empty($_POST['student_id'])) {
        $student_id = intval($_POST['student_id']);

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update students table to set active = 1
            $sql_activate = "UPDATE students SET active = 1 WHERE id = ?";
            $stmt = $conn->prepare($sql_activate);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $stmt->close();

            // Delete the block reason from student_block_reasons table
            $sql_delete_reason = "DELETE FROM student_block_reasons WHERE student_id = ?";
            $stmt = $conn->prepare($sql_delete_reason);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            echo "<script>
                    alert('Student has been unblocked successfully.');
                    window.location.href = 'view_students.php'; // Redirect to student list
                  </script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p>Error activating student: " . $e->getMessage() . "</p>";
        }
    }
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - <?php echo htmlspecialchars($student['student_name'] ?? ''); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #eef2f3;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-image {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #3498db;
            margin-bottom: 15px;
        }
        .details {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .detail-item {
            width: 48%;
            margin-bottom: 15px;
        }
        .detail-item label {
            font-weight: bold;
            color: #555;
        }
        .detail-item span {
            display: block;
            margin-top: 5px;
            color: #333;
        }
        .section {
            margin-top: 30px;
        }
        .section h3 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            color: #3498db;
            margin-bottom: 15px;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #777;
            margin-top: 10px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin: 20px 0;
        }
        .back-link a {
            text-decoration: none;
            color: #fff;
            background-color: #3498db;
            padding: 10px 15px;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .back-link a:hover {
            background-color: #2980b9;
        }
        .buttons {
            position: absolute;
            top: 30px;
            right: 30px;
        }
        .buttons button,
        .buttons a {
            margin: 0 10px;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .buttons .print-btn {
            background-color: #2ecc71;
        }
        .buttons .print-btn:hover {
            background-color: #27ae60;
        }
        .buttons .delete-btn {
            background-color: #e74c3c;
        }
        .buttons .delete-btn:hover {
            background-color: #c0392b;
        }
        .edits{
            background: #2ecc71;
        }

        .back-btn {
        text-decoration: none;
        color: #fff;
        background-color: #3498db;
        padding: 10px 15px;
        border-radius: 5px;
        font-weight: bold;
        transition: background-color 0.3s;
    }
    .back-btn:hover {
        background-color: #2980b9;
    }
    /* Block Button Styles */
    .block-btn {
        background-color: #f44336; /* Red background */
        color: white; /* White text */
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .block-btn:hover {
        background-color: #d32f2f; /* Darker red on hover */
    }

    .block-btn:focus {
        outline: none;
    }

    .active-btn {
        background-color:rgb(47, 211, 85); /* Red background */
        color: white; /* White text */
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .active-btn:hover {
        background-color:rgb(47, 211, 85); /* Darker red on hover */
    }

    .active-btn:focus {
        outline: none;
    }

/* Modal Overlay (background) */
#blockModalOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
    display: none; /* Hidden by default */
    z-index: 9999; /* Ensure it is on top of other content */
}

/* Modal Dialog Box */
#blockModal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    width: 400px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: none; /* Hidden by default */
    z-index: 10000; /* Ensure modal is above the overlay */
}

/* Modal Title */
#blockModal h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

/* Textarea for Block Reason */
#blockModal textarea {
    width: 100%;
    height: 100px;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    resize: none;
}

/* Button inside Modal */
#blockModal button {
    background-color: #f44336; /* Red background */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 10px;
    transition: background-color 0.3s ease;
}

#blockModal button:hover {
    background-color: #d32f2f; /* Darker red on hover */
}

/* Cancel Button */
#blockModal .cancel-btn {
    background-color: #ccc; /* Light gray background */
    color: #333;
    margin-left: 10px;
}

#blockModal .cancel-btn:hover {
    background-color: #999; /* Darker gray on hover */
}

/* Close Modal when clicking outside */
#blockModalOverlay {
    cursor: pointer; /* Pointer cursor to indicate it is clickable */
}

        /* Print Styles */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .container {
                box-shadow: none;
                border: none;
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>

    <script>
        function confirmDeletion() {
            return confirm("Are you sure you want to delete this student? This action cannot be undone.");
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="buttons no-print">
            <button class="print-btn" onclick="window.print()">Print</button>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this student?');" style="display: inline;">
                <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                <button type="submit" name="delete" class="delete-btn">Delete</button>
            </form>

            <a href="./edit_student.php?id=<?php echo $student_id; ?>" class="edits">Edit</a>

            <?php if (isset($student['active']) && intval($student['active']) === 1): ?>
                <!-- Block Button -->
                <button onclick="openBlockModal('<?php echo htmlspecialchars($student['student_id']); ?>')" class="block-btn">
                    Block
                </button>
            <?php else: ?>
                <!-- Activate Button -->
                <form method="POST" onsubmit="return confirm('Are you sure you want to activate this student?');" style="display: inline;">
                    <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                    <button type="submit" name="activate" class="active-btn">Activate</button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (isset($student) && $student): ?>
            <h2><?php echo htmlspecialchars($student['student_name']); ?>'s Profile</h2>
            <div class="profile-header">
                <?php if (!empty($student['student_image']) && file_exists($student['student_image'])): ?>
                    <img class="profile-image" src="<?php echo htmlspecialchars($student['student_image']); ?>" alt="Profile Image">
                <?php else: ?>
                    <img class="profile-image" src="./Resources/default_profile.png" alt="No Image">
                <?php endif; ?>
            </div>

            <!-- Student Details Section -->
            <div class="section">
                <h3>Student Details</h3>
                <div class="details">
                    <div class="detail-item">
                        <label>Full Name:</label>
                        <span><?php echo htmlspecialchars($student['student_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Student ID:</label>
                        <span><?php echo htmlspecialchars($student['username']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Gender:</label>
                        <span><?php echo htmlspecialchars($student['student_gender'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Date of Birth:</label>
                        <span><?php echo htmlspecialchars($student['student_dob'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Address:</label>
                        <span><?php echo htmlspecialchars($student['student_address'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Phone:</label>
                        <span><?php echo htmlspecialchars($student['student_phone'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Nationality:</label>
                        <span><?php echo htmlspecialchars($student['student_nationality'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Religion:</label>
                        <span><?php echo htmlspecialchars($student['student_religion'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Mother Tongue:</label>
                        <span><?php echo htmlspecialchars($student['student_mother_tongue'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Assigned Grade:</label>
                        <span><?php echo htmlspecialchars($student['grade_name'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Past Education Section -->
            <div class="section">
                <h3>Past Education</h3>
                <div class="details">
                    <div class="detail-item">
                        <label>School Attended:</label>
                        <span><?php echo htmlspecialchars($student['school_attended'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>School Address:</label>
                        <span><?php echo htmlspecialchars($student['school_address'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>School Medium:</label>
                        <span><?php echo htmlspecialchars($student['school_medium'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Second Language:</label>
                        <span><?php echo htmlspecialchars($student['second_language'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Grade Passed:</label>
                        <span><?php echo htmlspecialchars($student['grade_passed'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Last Attended:</label>
                        <span><?php echo htmlspecialchars($student['last_attended'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Duration of Stay:</label>
                        <span><?php echo htmlspecialchars($student['duration_of_stay'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Reason for Leaving:</label>
                        <span><?php echo htmlspecialchars($student['reason_for_leaving'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Special Attention:</label>
                        <span><?php echo htmlspecialchars($student['special_attention'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Father's Details Section -->
            <div class="section">
                <h3>Father's Details</h3>
                <div class="details">
                    <div class="detail-item">
                        <label>Name:</label>
                        <span><?php echo htmlspecialchars($student['father_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Date of Birth:</label>
                        <span><?php echo htmlspecialchars($student['father_dob'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Occupation:</label>
                        <span><?php echo htmlspecialchars($student['father_occupation'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Mobile:</label>
                        <span><?php echo htmlspecialchars($student['father_mobile'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($student['father_email'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Residence:</label>
                        <span><?php echo htmlspecialchars($student['father_residence'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>School:</label>
                        <span><?php echo htmlspecialchars($student['father_school'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Education:</label>
                        <span><?php echo htmlspecialchars($student['father_education'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Mother's Details Section -->
            <div class="section">
                <h3>Mother's Details</h3>
                <div class="details">
                    <div class="detail-item">
                        <label>Name:</label>
                        <span><?php echo htmlspecialchars($student['mother_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Date of Birth:</label>
                        <span><?php echo htmlspecialchars($student['mother_dob'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Occupation:</label>
                        <span><?php echo htmlspecialchars($student['mother_occupation'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Mobile:</label>
                        <span><?php echo htmlspecialchars($student['mother_mobile'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($student['mother_email'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Residence:</label>
                        <span><?php echo htmlspecialchars($student['mother_residence'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>School:</label>
                        <span><?php echo htmlspecialchars($student['mother_school'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Education:</label>
                        <span><?php echo htmlspecialchars($student['mother_education'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Displaying Sibling Details -->
            <div class="section">
                <h3>Siblings</h3>
                <div class="details">
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
                </div>
            </div>


            <!-- Guardian's Details Section -->
            <div class="section">
                <h3>Guardian's Details</h3>
                <div class="details">
                    <div class="detail-item">
                        <label>Name:</label>
                        <span><?php echo htmlspecialchars($student['guardian_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Relation:</label>
                        <span><?php echo htmlspecialchars($student['guardian_relationship'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Mobile:</label>
                        <span><?php echo htmlspecialchars($student['guardian_mobile'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($student['guardian_email'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Residence:</label>
                        <span><?php echo htmlspecialchars($student['guardian_residence'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>School:</label>
                        <span><?php echo htmlspecialchars($student['guardian_school'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Education:</label>
                        <span><?php echo htmlspecialchars($student['guardian_education'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact Section -->
            <div class="section">
                <h3>Emergency Contact Details</h3>
                <div class="details">
                    <div class="detail-item">
                        <label>Name:</label>
                        <span><?php echo htmlspecialchars($student['emergency_contact_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Relation:</label>
                        <span><?php echo htmlspecialchars($student['emergency_relationship'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Mobile:</label>
                        <span><?php echo htmlspecialchars($student['emergency_mobile'] ?? 'N/A'); ?></span>
                    </div>
                    </div>
                    <div class="detail-item">
                        <label>Residence:</label>
                        <span><?php echo htmlspecialchars($student['emergency_residence'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Office:</label>
                        <span><?php echo htmlspecialchars($student['emergency_office'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Fax:</label>
                        <span><?php echo htmlspecialchars($student['emergency_fax'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>
                    
            <!-- Back Button -->
            <div class="back-link">
                <a href="view_students.php" class="back-btn">Back to Student Details</a>
            </div>

            <?php else: ?>
                <p>No student data found.</p>
            <?php endif; ?>
    </div>

    <!-- Block Modal -->
    <div id="blockModalOverlay" onclick="closeBlockModal()"></div>
    <div id="blockModal">
        <form method="POST">
            <input type="hidden" name="student_id" id="blockStudentId">
            <label for="blockReason">Reason for Blocking:</label>
            <textarea name="block_reason" id="blockReason" rows="4" required></textarea>
            <button type="submit" name="block">Block</button>
            <button type="button" onclick="closeBlockModal()">Cancel</button>
        </form>
    </div>


</body>
<script>
    function openBlockModal(studentId) {
    document.getElementById('blockStudentId').value = studentId;
    document.getElementById('blockModalOverlay').style.display = 'block';
    document.getElementById('blockModal').style.display = 'block';
    }

    function closeBlockModal() {
        document.getElementById('blockModalOverlay').style.display = 'none';
        document.getElementById('blockModal').style.display = 'none';
    }
    </script>

</html>

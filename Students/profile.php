<?php
session_start();

include("database_connection.php");

// Check if the user is logged in and is a Student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

// Fetch student username from session
$username = $_SESSION['user']['username'];

// SQL query to fetch student details
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
WHERE s.username = ?
";

// Prepare and execute the statement
$stmt = $conn->prepare($query);
if ($stmt === false) {
    echo "Error preparing statement: " . htmlspecialchars($conn->error);
    exit;
}

$stmt->bind_param("s", $username);
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

// Fetch sibling details based on student ID
$sibling_query = "SELECT * FROM siblings WHERE student_admission_id = ?";
$sibling_stmt = $conn->prepare($sibling_query);
$sibling_stmt->bind_param("i", $student['student_id']);
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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

        .edits {
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
            background-color: #f44336;
            /* Red background */
            color: white;
            /* White text */
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .block-btn:hover {
            background-color: #d32f2f;
            /* Darker red on hover */
        }

        .block-btn:focus {
            outline: none;
        }

        /* Modal Overlay (background) */
        #blockModalOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            /* Semi-transparent black */
            display: none;
            /* Hidden by default */
            z-index: 9999;
            /* Ensure it is on top of other content */
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
            display: none;
            /* Hidden by default */
            z-index: 10000;
            /* Ensure modal is above the overlay */
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
            background-color: #f44336;
            /* Red background */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        #blockModal button:hover {
            background-color: #d32f2f;
            /* Darker red on hover */
        }

        /* Cancel Button */
        #blockModal .cancel-btn {
            background-color: #ccc;
            /* Light gray background */
            color: #333;
            margin-left: 10px;
        }

        #blockModal .cancel-btn:hover {
            background-color: #999;
            /* Darker gray on hover */
        }

        /* Close Modal when clicking outside */
        #blockModalOverlay {
            cursor: pointer;
            /* Pointer cursor to indicate it is clickable */
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

        /* Responsive Design */
@media screen and (max-width: 768px) {
    .details {
        flex-direction: column;
    }

    .detail-item {
        width: 100%;
    }

    .detail-item span {
        word-wrap: break-word; /* Break long words to the next line */
        overflow-wrap: break-word; /* Ensures better compatibility */
        white-space: normal; /* Allows text to wrap onto the next line */
    }
}

@media screen and (max-width: 480px) {
    .container {
        padding: 20px 10px; /* Reduce padding for smaller screens */
    }

    .profile-image {
        width: 120px; /* Adjust image size for smaller screens */
        height: 120px;
    }

    .buttons {
        position: static;
        margin-bottom: 10px; /* Space between buttons and content */
        text-align: center; /* Center align buttons */
    }

    .buttons a,
    .buttons button {
        margin: 5px 0; /* Stack buttons with some margin */
        width: 100%; /* Make buttons full width */
        max-width: 300px; /* Optional: limit max width of buttons */
    }

    .section h3 {
        font-size: 18px; /* Reduce heading size for smaller screens */
    }

    .back-link a {
        padding: 8px 10px; /* Adjust padding for smaller buttons */
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
            <a href="./edit_student.php" class="edits">Edit</a>
        </div>

        <?php if (isset($student) && $student): ?>
            <h2><?php echo htmlspecialchars($student['student_name']); ?>'s Profile</h2>
            <div class="profile-header">
                <?php
                // Adjust the path to correctly point to the parent directory for the uploaded images
                $imagePath = "../" . htmlspecialchars($student['student_image']);
                $defaultImagePath = "../Resources/default_profile.png";
                ?>
                <?php if (!empty($student['student_image']) && file_exists($imagePath)): ?>
                    <img class="profile-image" src="<?php echo $imagePath; ?>" alt="Profile Image">
                <?php else: ?>
                    <img class="profile-image" src="<?php echo $defaultImagePath; ?>" alt="No Image">
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
        <a href="Student_Dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>

<?php else: ?>
    <p>No student data found.</p>
<?php endif; ?>
</div>

<div id="blockModalOverlay" style="display: none;"></div>
<div id="blockModal" style="display: none;">
    <form method="POST" id="blockForm">
        <h2>Block Student</h2>
        <input type="hidden" name="student_id" id="blockStudentId">
        <textarea name="block_reason" placeholder="Enter block reason..." required></textarea>
        <button type="submit" name="block" class="block-btn">Block</button>
        <button type="button" onclick="closeBlockModal();" class="cancel-btn">Cancel</button>
    </form>
</div>


</body>
<script>
    function openBlockModal(studentId) {
        // Set the student ID in the modal
        document.getElementById('blockStudentId').value = studentId;
        // Show the modal
        document.getElementById('blockModalOverlay').style.display = 'block';
        document.getElementById('blockModal').style.display = 'block';
    }

    function closeBlockModal() {
        // Hide the modal
        document.getElementById('blockModalOverlay').style.display = 'none';
        document.getElementById('blockModal').style.display = 'none';
    }

    // Handle form submission
    document.getElementById('blockForm').onsubmit = function(event) {
        event.preventDefault(); // Prevent default form submission

        // Collect the form data
        var formData = new FormData(document.getElementById('blockForm'));

        // Make the POST request using Fetch API
        fetch(window.location.href, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.text())
            .then(data => {
                // Close the modal after successful submission
                closeBlockModal();
                alert('Student has been blocked successfully!');
                window.location.reload(); // Reload the page to reflect changes
            })
            .catch(error => {
                console.error('Error blocking student:', error);
                alert('Failed to block student. Please try again.');
            });
    };
</script>

</html>
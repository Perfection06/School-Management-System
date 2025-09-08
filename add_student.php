<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Database connection
include("database_connection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Gather form data
    // Student details
    $student_name = $_POST['student_name'];
    $student_address = $_POST['student_address'];
    $student_dob = $_POST['student_dob'];
    $student_gender = $_POST['student_gender'];
    $student_nationality = $_POST['student_nationality'];
    $student_religion = $_POST['student_religion'];
    $student_mother_tongue = $_POST['student_mother_tongue'];
    $student_phone = $_POST['student_phone'];
    $assigning_grade = $_POST['assigning_grade'];
    $school_attended = $_POST['school_attended'];
    $school_address = $_POST['school_address'];
    $school_medium = $_POST['school_medium'];
    $second_language = $_POST['second_language'];
    $grade_passed = $_POST['grade_passed'];
    $last_attended = $_POST['last_attended'];
    $duration_of_stay = $_POST['duration_of_stay'];
    $reason_for_leaving = $_POST['reason_for_leaving'];
    $special_attention = $_POST['special_attention'];

    // Parent and Guardian details (father, mother, guardian)
    $father_name = $_POST['father_name'];
    $father_id = $_POST['father_id'];
    $father_dob = $_POST['father_dob'];
    $father_occupation = $_POST['father_occupation'];
    $father_school = $_POST['father_school'];
    $father_education = $_POST['father_education'];
    $father_mobile = $_POST['father_mobile'];
    $father_residence = $_POST['father_residence'];
    $father_email = $_POST['father_email'];

    $mother_name = $_POST['mother_name'];
    $mother_id = $_POST['mother_id'];
    $mother_dob = $_POST['mother_dob'];
    $mother_occupation = $_POST['mother_occupation'];
    $mother_school = $_POST['mother_school'];
    $mother_education = $_POST['mother_education'];
    $mother_mobile = $_POST['mother_mobile'];
    $mother_residence = $_POST['mother_residence'];
    $mother_email = $_POST['mother_email'];

    $guardian_name = $_POST['guardian_name'];
    $guardian_id = $_POST['guardian_id'];
    $guardian_dob = $_POST['guardian_dob'];
    $guardian_occupation = $_POST['guardian_occupation'];
    $guardian_school = $_POST['guardian_school'];
    $guardian_education = $_POST['guardian_education'];
    $guardian_mobile = $_POST['guardian_mobile'];
    $guardian_residence = $_POST['guardian_residence'];
    $guardian_email = $_POST['guardian_email'];
    $guardian_relationship = $_POST['guardian_relationship'];
    $guardian_reason = $_POST['guardian_reason'];

    $parents_together = $_POST['parents_together'];
    $parents_reason = $_POST['parents_reason'];
    $remarks = $_POST['remarks'];

    // Emergency contact details
    $emergency_name = $_POST['emergency_name'];
    $emergency_relationship = $_POST['emergency_relationship'];
    $emergency_mobile = $_POST['emergency_mobile'];
    $emergency_residence = $_POST['emergency_residence'];
    $emergency_office = $_POST['emergency_office'];
    $emergency_fax = $_POST['emergency_fax'];

    // Handle file uploads for student image and signature
    $student_image = '';
    if (isset($_FILES['student_image']) && $_FILES['student_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $student_image = $target_dir . basename($_FILES["student_image"]["name"]);
        move_uploaded_file($_FILES["student_image"]["tmp_name"], $student_image);
    }

    $signature_image = '';
    if (isset($_FILES['signature_image']) && $_FILES['signature_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $signature_image = $target_dir . basename($_FILES["signature_image"]["name"]);
        move_uploaded_file($_FILES["signature_image"]["tmp_name"], $signature_image);
    }

    // SQL statement to insert student record58
    $sql = "INSERT INTO Student_Admissions (
        student_name, student_address, student_dob, student_gender, student_nationality,
        student_religion, student_mother_tongue, student_image, student_phone, assigning_grade,
        school_attended, school_address, school_medium, second_language, grade_passed, last_attended,
        duration_of_stay, reason_for_leaving, special_attention, father_name, father_id, father_dob,
        father_occupation, father_school, father_education, father_mobile, father_residence,
        father_email, mother_name, mother_id, mother_dob, mother_occupation, mother_school,
        mother_education, mother_mobile, mother_residence, mother_email, guardian_name, guardian_id,
        guardian_dob, guardian_occupation, guardian_school, guardian_education, guardian_mobile,
        guardian_residence, guardian_email, guardian_relationship, guardian_reason, parents_together,
        parents_reason, remarks, emergency_name, emergency_relationship, emergency_mobile,
        emergency_residence, emergency_office, emergency_fax, signature_image
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "ssssssssssssssssssssssssssssssssssssssssssssssssssssssssss",
        $student_name,
        $student_address,
        $student_dob,
        $student_gender,
        $student_nationality,
        $student_religion,
        $student_mother_tongue,
        $student_image,
        $student_phone,
        $assigning_grade,
        $school_attended,
        $school_address,
        $school_medium,
        $second_language,
        $grade_passed,
        $last_attended,
        $duration_of_stay,
        $reason_for_leaving,
        $special_attention,
        $father_name,
        $father_id,
        $father_dob,
        $father_occupation,
        $father_school,
        $father_education,
        $father_mobile,
        $father_residence,
        $father_email,
        $mother_name,
        $mother_id,
        $mother_dob,
        $mother_occupation,
        $mother_school,
        $mother_education,
        $mother_mobile,
        $mother_residence,
        $mother_email,
        $guardian_name,
        $guardian_id,
        $guardian_dob,
        $guardian_occupation,
        $guardian_school,
        $guardian_education,
        $guardian_mobile,
        $guardian_residence,
        $guardian_email,
        $guardian_relationship,
        $guardian_reason,
        $parents_together,
        $parents_reason,
        $remarks,
        $emergency_name,
        $emergency_relationship,
        $emergency_mobile,
        $emergency_residence,
        $emergency_office,
        $emergency_fax,
        $signature_image
    );

    // Execute the statement
    if ($stmt->execute()) {
        // Get the last inserted student admission ID
        $student_admission_id = $conn->insert_id;

        // Handle sibling details if available
        if (isset($_POST['sibling_name']) && count($_POST['sibling_name']) > 0) {
            $siblings = $_POST['sibling_name'];
            $genders = $_POST['sibling_gender'];
            $dobs = $_POST['sibling_dob'];
            $schools = $_POST['sibling_school'];
            $grades = $_POST['sibling_grade'];

            // Prepare SQL for inserting sibling details
            $insertSiblingQuery = "INSERT INTO siblings (student_admission_id, name, gender, dob, school, grade) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insertSiblingQuery);

            // Loop through all siblings and insert them
            foreach ($siblings as $index => $sibling) {
                $stmt->bind_param("isssss", $student_admission_id, $siblings[$index], $genders[$index], $dobs[$index], $schools[$index], $grades[$index]);
                $stmt->execute();
            }

            // Commit the transaction for siblings
            $stmt->close();
        }

        echo "<script>
            alert('New student admission record and sibling details created successfully.');
            window.location.href = 'pending_admissions.php';
        </script>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Admission Form - Reliance International School</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        h2,
        h3,
        h4 {
            color: #333;
        }

        .form-section {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .col {
            flex: 1;
            min-width: 200px;
        }

        .btn-add {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .btn-add:hover {
            background-color: #45a049;
        }

        .signature-input {
            display: block;
            margin: 20px 0;
        }

        #parents_reason_container {
            display: none;
        }

        #signature_preview {
            display: none;
            max-width: 200px;
            margin-top: 10px;
        }

        #submit_btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <h2>Student Admission Form</h2>

    <form action="./add_student.php" method="post" enctype="multipart/form-data">


        <!-- Student Details Section -->
        <div class="form-section">
            <h3>Student Details</h3>
            <label>Name:</label>
            <input type="text" name="student_name" required>

            <label>Address:</label>
            <textarea name="student_address" required></textarea>

            <label>Date of Birth:</label>
            <input type="date" name="student_dob" required>

            <label>Gender:</label>
            <select name="student_gender" required>
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
            </select>

            <label>Nationality:</label>
            <input type="text" name="student_nationality" required>

            <label>Religion:</label>
            <input type="text" name="student_religion" required>

            <label>Mother Tongue:</label>
            <input type="text" name="student_mother_tongue" required>

            <label>Student Image:</label>
            <input type="file" name="student_image" accept="image/*">

            <label>Phone Number:</label>
            <input type="tel" name="student_phone" required>

            <label>Assigning Grade:</label>
            <input type="text" name="assigning_grade" required>
        </div>

        <!-- Past Education Section -->
        <div class="form-section">
            <h3>Past Education (if applicable)</h3>

            <label>School Attended:</label>
            <input type="text" name="school_attended">

            <label>Address of School:</label>
            <textarea name="school_address"></textarea>

            <label>Medium:</label>
            <input type="text" name="school_medium">

            <label>Second Language:</label>
            <input type="text" name="second_language">

            <label>Grade Passed:</label>
            <input type="text" name="grade_passed">

            <label>Last Attended:</label>
            <input type="date" name="last_attended">

            <label>Duration of Stay:</label>
            <input type="text" name="duration_of_stay">

            <label>Reason for Leaving:</label>
            <textarea name="reason_for_leaving"></textarea>

            <label>If any special attention required:</label>
            <textarea name="special_attention"></textarea>
        </div>


        <!-- Family Details Section -->
        <div class="form-section">
            <h3>Family Details</h3>

            <!-- Father's Details -->
            <h4>Father's Details</h4>
            <label>Full Name:</label>
            <input type="text" name="father_name" required>

            <label>NIC/DL or Passport ID Number:</label>
            <input type="text" name="father_id" required>

            <label>Date of Birth:</label>
            <input type="date" name="father_dob" required>

            <label>Occupation:</label>
            <input type="text" name="father_occupation" required>

            <label>School Attended:</label>
            <input type="text" name="father_school">

            <label>Educations and Qualifications:</label>
            <textarea name="father_education"></textarea>

            <!-- Father's Contact Information -->
            <h4>Contact Information</h4>
            <label>Mobile:</label>
            <input type="tel" name="father_mobile" required>

            <label>Residence:</label>
            <input type="tel" name="father_residence">

            <label>Email:</label>
            <input type="email" name="father_email">

            <!-- Mother's Details -->
            <h4>Mother's Details</h4>
            <label>Full Name:</label>
            <input type="text" name="mother_name" required>

            <label>NIC/DL or Passport ID Number:</label>
            <input type="text" name="mother_id" required>

            <label>Date of Birth:</label>
            <input type="date" name="mother_dob" required>

            <label>Occupation:</label>
            <input type="text" name="mother_occupation" required>

            <label>School Attended:</label>
            <input type="text" name="mother_school">

            <label>Educations and Qualifications:</label>
            <textarea name="mother_education"></textarea>

            <!-- Mother's Contact Information -->
            <h4>Contact Information</h4>
            <label>Mobile:</label>
            <input type="tel" name="mother_mobile" required>

            <label>Residence:</label>
            <input type="tel" name="mother_residence">

            <label>Email:</label>
            <input type="email" name="mother_email">

            <!-- Guardian's Details -->
            <h4>Guardian's Details</h4>
            <label>Full Name:</label>
            <input type="text" name="guardian_name" required>

            <label>NIC/DL or Passport ID Number:</label>
            <input type="text" name="guardian_id" required>

            <label>Date of Birth:</label>
            <input type="date" name="guardian_dob" required>

            <label>Occupation:</label>
            <input type="text" name="guardian_occupation" required>

            <label>School Attended:</label>
            <input type="text" name="guardian_school">

            <label>Educations and Qualifications:</label>
            <textarea name="guardian_education"></textarea>

            <!-- Guardian's Contact Information -->
            <h4>Contact Information</h4>
            <label>Mobile:</label>
            <input type="tel" name="guardian_mobile" required>

            <label>Residence:</label>
            <input type="tel" name="guardian_residence">

            <label>Email:</label>
            <input type="email" name="guardian_email">

            <label>Relationship of Guardian to Student:</label>
            <input type="text" name="guardian_relationship">

            <label>Reason why the child is living with the guardian:</label>
            <textarea name="guardian_reason"></textarea>
        </div>

        <!-- Details of Siblings Section -->
        <div class="form-section">
            <h3>Details of Siblings</h3>
            <div id="siblings_container">
                <div class="row sibling">
                    <div class="col">
                        <label>Name:</label>
                        <input type="text" name="sibling_name[]" required>
                    </div>
                    <div class="col">
                        <label>Gender:</label>
                        <select name="sibling_gender[]" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    <div class="col">
                        <label>Date of Birth:</label>
                        <input type="date" name="sibling_dob[]" required>
                    </div>
                    <div class="col">
                        <label>School:</label>
                        <input type="text" name="sibling_school[]" required>
                    </div>
                    <div class="col">
                        <label>Grade:</label>
                        <input type="text" name="sibling_grade[]" required>
                    </div>
                </div>
            </div>
            <button class="btn-add" type="button" onclick="addSibling()">Add Another Sibling</button>
        </div>



        <!-- Particulars of the Family Section -->
        <div class="form-section">
            <h3>Particulars of the Family</h3>
            <label>Are Father and Mother of the child living together?</label>
            <input type="radio" name="parents_together" value="yes" id="parents_yes" required> Yes
            <input type="radio" name="parents_together" value="no" id="parents_no" required> No

            <div id="parents_reason_container">
                <label>If "No," please specify reasons:</label>
                <textarea name="parents_reason"></textarea>
            </div>

            <label>Remarks:</label>
            <textarea name="remarks"></textarea>
        </div>

        <!-- Emergency Contact Section -->
        <div class="form-section">
            <h3>Emergency Contact</h3>
            <label>Name:</label>
            <input type="text" name="emergency_name" required>

            <label>Relationship to the Student:</label>
            <input type="text" name="emergency_relationship" required>

            <label>Mobile:</label>
            <input type="tel" name="emergency_mobile" required>

            <label>Residence:</label>
            <input type="tel" name="emergency_residence">

            <label>Office:</label>
            <input type="tel" name="emergency_office">

            <label>Fax:</label>
            <input type="text" name="emergency_fax">
        </div>

        <!-- Declaration Section -->
        <div class="form-section">
            <h3>Declaration</h3>
            <p>In the event of the named child being admitted to Reliance International School, I hereby agree to abide by the rules, regulations, and conditions of the school. Also, I understand that the provision of false or inaccurate information will terminate the offer of a place.</p>

            <label>
                I agree to the terms and conditions.
                <input type="checkbox" id="agree_checkbox" required>
            </label>

            <label>Signature of Father/Mother/Guardian:</label>
            <input class="signature-input" type="file" name="signature_image" accept="image/*" required>
            <img id="signature_preview" src="#" alt="Signature Preview" />


            <button type="submit" id="submit_btn" disabled>Submit</button>
    </form>

    <!-- JavaScript Section -->
    <script>
        // Function to add another sibling
        function addSibling() {
            const siblingsContainer = document.getElementById('siblings_container');
            const newSibling = document.createElement('div');
            newSibling.classList.add('row', 'sibling');
            newSibling.innerHTML = `
                <div class="col">
                    <label>Name:</label>
                    <input type="text" name="sibling_name[]" >
                </div>
                <div class="col">
                    <label>Gender:</label>
                    <select name="sibling_gender[]" >
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div class="col">
                    <label>Date of Birth:</label>
                    <input type="date" name="sibling_dob[]" >
                </div>
                <div class="col">
                    <label>School:</label>
                    <input type="text" name="sibling_school[]" >
                </div>
                <div class="col">
                    <label>Grade:</label>
                    <input type="text" name="sibling_grade[]" >
                </div>
            `;
            siblingsContainer.appendChild(newSibling);
        }


        // Show/Hide reason for parents not living together
        document.addEventListener('DOMContentLoaded', function() {
            const parentsYes = document.getElementById('parents_yes');
            const parentsNo = document.getElementById('parents_no');
            const reasonContainer = document.getElementById('parents_reason_container');

            parentsYes.addEventListener('change', function() {
                if (this.checked) {
                    reasonContainer.style.display = 'none';
                }
            });

            parentsNo.addEventListener('change', function() {
                if (this.checked) {
                    reasonContainer.style.display = 'block';
                }
            });

            // Enable Submit button only when agree checkbox is checked
            const agreeCheckbox = document.getElementById('agree_checkbox');
            const submitBtn = document.getElementById('submit_btn');

            agreeCheckbox.addEventListener('change', function() {
                submitBtn.disabled = !this.checked;
            });

            // Preview signature image immediately after upload
            const signatureInput = document.querySelector('input[name="signature_image"]');
            const signaturePreview = document.getElementById('signature_preview');

            signatureInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        signaturePreview.src = e.target.result;
                        signaturePreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    signaturePreview.src = '#';
                    signaturePreview.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>
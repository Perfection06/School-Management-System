<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}


// Include database connection
include('database_connection.php');

// Check if ID is provided
if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // Fetch current data for pre-filling the form
    $query = "
        SELECT 
            s.id AS student_id,
            s.name AS student_name,
            s.username,
            s.grade_id,
            sa.*
        FROM Students s
        LEFT JOIN Student_Admissions sa ON s.id = sa.id
        WHERE s.id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();

    // Fetch existing siblings' details
    $sibling_query = "SELECT * FROM siblings WHERE student_admission_id = ?";
    $sibling_stmt = $conn->prepare($sibling_query);
    $sibling_stmt->bind_param("i", $student_id);
    $sibling_stmt->execute();
    $sibling_result = $sibling_stmt->get_result();

    $siblings = [];
    while ($sibling = $sibling_result->fetch_assoc()) {
        $siblings[] = $sibling;
    }

    // Fetch available grades
    $gradesQuery = "SELECT id, grade_name FROM grades";
    $gradesResult = $conn->query($gradesQuery);
    $grades = [];
    while ($row = $gradesResult->fetch_assoc()) {
        $grades[] = $row;
    }

} else {
    echo "No student ID provided.";
    exit;
}

// Handle form submission to update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Start a transaction
    $conn->begin_transaction();

    try {
        // Handle file upload for the profile image
        if (!empty($_FILES['profile_image']['name'])) {
            $image_name = $_FILES['profile_image']['name'];
            $image_tmp = $_FILES['profile_image']['tmp_name'];
            $image_path = "uploads/" . $image_name;

            // Move the uploaded file to the 'uploads' folder
            if (!move_uploaded_file($image_tmp, $image_path)) {
                throw new Exception("Failed to move uploaded file.");
            }
        } else {
            // If no new image is uploaded, use the existing one
            $image_path = $student['student_image']; // Use existing image from Student_Admissions table
        }


        // Update Students table query (do not include student_image here)
        $updateStudentQuery = "
            UPDATE Students 
            SET name = ?, username = ?, grade_id = ?
            WHERE id = ?
        ";

        // Prepare and bind parameters for Students table update
        $stmt1 = $conn->prepare($updateStudentQuery);
        $stmt1->bind_param("ssii", $_POST['name'], $_POST['username'], $_POST['grade_id'], $student_id);
        $stmt1->execute();
        $stmt1->close();

        // Update Student_Admissions table query to set student_image
        $updateAdmissionsQuery = "
            UPDATE Student_Admissions 
            SET 
                student_address = ?, student_dob = ?, student_gender = ?, student_nationality = ?, 
                student_phone = ?, student_religion = ?, student_mother_tongue = ?, school_attended = ?, 
                school_address = ?, school_medium = ?, second_language = ?, grade_passed = ?, 
                last_attended = ?, duration_of_stay = ?, reason_for_leaving = ?, special_attention = ?, 
                father_name = ?, father_dob = ?, father_occupation = ?, father_mobile = ?, 
                father_email = ?, father_school = ?, father_education = ?, father_residence = ?, 
                mother_name = ?, mother_dob = ?, mother_occupation = ?, mother_mobile = ?, 
                mother_email = ?, mother_school = ?, mother_education = ?, mother_residence = ?, 
                guardian_name = ?, guardian_relationship = ?, guardian_mobile = ?, guardian_email = ?, 
                guardian_residence = ?, siblings = ?, emergency_name = ?, emergency_relationship = ?, 
                emergency_mobile = ?, emergency_residence = ?, emergency_office = ?, emergency_fax = ?, 
                student_image = ? -- Update the image here
            WHERE id = ?
        ";

        // Prepare and bind parameters for Student_Admissions table update
        $stmt2 = $conn->prepare($updateAdmissionsQuery);
        $stmt2->bind_param(
            "sssssssssssssssssssssssssssssssssssssssssssssi",
            $_POST['student_address'], $_POST['student_dob'], $_POST['student_gender'], $_POST['student_nationality'],
            $_POST['student_phone'], $_POST['student_religion'], $_POST['student_mother_tongue'], $_POST['school_attended'],
            $_POST['school_address'], $_POST['school_medium'], $_POST['second_language'], $_POST['grade_passed'],
            $_POST['last_attended'], $_POST['duration_of_stay'], $_POST['reason_for_leaving'], $_POST['special_attention'],
            $_POST['father_name'], $_POST['father_dob'], $_POST['father_occupation'], $_POST['father_mobile'],
            $_POST['father_email'], $_POST['father_school'], $_POST['father_education'], $_POST['father_residence'],
            $_POST['mother_name'], $_POST['mother_dob'], $_POST['mother_occupation'], $_POST['mother_mobile'],
            $_POST['mother_email'], $_POST['mother_school'], $_POST['mother_education'], $_POST['mother_residence'],
            $_POST['guardian_name'], $_POST['guardian_relationship'], $_POST['guardian_mobile'], $_POST['guardian_email'],
            $_POST['guardian_residence'], $_POST['siblings'], $_POST['emergency_name'], $_POST['emergency_relationship'],
            $_POST['emergency_mobile'], $_POST['emergency_residence'], $_POST['emergency_office'], $_POST['emergency_fax'],
            $image_path, $student_id
        );
        $stmt2->execute();
        $stmt2->close();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process existing siblings
            if (isset($_POST['siblings'])) {
                foreach ($_POST['siblings'] as $sibling) {
                    // Check if sibling ID exists (to update) or if it's a new sibling (to insert)
                    if (isset($sibling['id']) && !empty($sibling['id'])) {
                        // Update existing sibling
                        $update_query = "UPDATE siblings SET name = ?, gender = ?, dob = ?, school = ?, grade = ? WHERE id = ?";
                        $update_stmt = $conn->prepare($update_query);
                        $update_stmt->bind_param("sssssi", $sibling['name'], $sibling['gender'], $sibling['dob'], $sibling['school'], $sibling['grade'], $sibling['id']);
                        $update_stmt->execute();
                    } else {
                        // Insert new sibling
                        $insert_query = "INSERT INTO siblings (student_admission_id, name, gender, dob, school, grade) VALUES (?, ?, ?, ?, ?, ?)";
                        $insert_stmt = $conn->prepare($insert_query);
                        $insert_stmt->bind_param("isssss", $student_id, $sibling['name'], $sibling['gender'], $sibling['dob'], $sibling['school'], $sibling['grade']);
                        $insert_stmt->execute();
                    }
                }
            }
        
            // Handle deletions
            if (isset($_POST['deleted_siblings'])) {
                $deletedSiblings = json_decode($_POST['deleted_siblings'], true);
                if (!empty($deletedSiblings)) {
                    // Delete siblings from the database
                    foreach ($deletedSiblings as $siblingId) {
                        $delete_query = "DELETE FROM siblings WHERE id = ?";
                        $delete_stmt = $conn->prepare($delete_query);
                        $delete_stmt->bind_param("i", $siblingId);
                        $delete_stmt->execute();
                    }
                }
            }
        }
        


        // Commit the transaction
        $conn->commit();

        // Echo success message as an alert
        echo "<script>alert('Profile updated successfully.'); window.location.href = 'student_profile.php?id=" . urlencode($student_id) . "';</script>";
        exit;
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();

        // Echo error message as an alert
        echo "<script>alert('Failed to update profile. Error: " . addslashes($e->getMessage()) . "'); window.location.href = 'student_profile.php?id=" . urlencode($student_id) . "';</script>";
        exit;
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            line-height: 1.6;
            background-color: #f4f4f4;
        }

        form {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .image-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .image-container img {
            max-width: 150px;
            height: auto;
            display: block;
            margin: auto;
            border: 2px solid #ccc;
            border-radius: 10px;
        }

        .preview-label {
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body>
    
    <h2>Edit Student Profile</h2>
    <form method="POST" action="edit_student.php?id=<?php echo htmlspecialchars($student['student_id']); ?>"  enctype="multipart/form-data">

    <div class="image-container">
        <label for="profile_image">Profile Image:</label>
        <?php
        $imageSrc = !empty($student['profile_image']) ? 'uploads/' . $student['profile_image'] : 'default-image.png';
        ?>
        <img id="image-preview" src="<?php echo htmlspecialchars($student['student_image']); ?>" alt="Existing Profile Image">
        <span class="preview-label">Existing image preview</span>
    </div>
    <input type="file" name="profile_image" id="profile_image" accept="image/*" onchange="previewImage(event)">

        <!-- Basic Information -->
        <label for="name">Full Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($student['student_name']); ?>" required><br>

        <label for="username">Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($student['username']); ?>" required><br>

        <label for="grade">Grade:</label>
        <select name="grade_id" required>
            <?php foreach ($grades as $grade): ?>
                <option value="<?php echo htmlspecialchars($grade['id']); ?>" 
                    <?php echo ($student['grade_id'] == $grade['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($grade['grade_name']); ?>
                </option>
            <?php endforeach; ?>
        </select><br>


        <label for="student_address">Address:</label>
        <input type="text" name="student_address" value="<?php echo htmlspecialchars($student['student_address']); ?>"><br>

        <label for="student_dob">Date of Birth:</label>
        <input type="date" name="student_dob" value="<?php echo htmlspecialchars($student['student_dob']); ?>"><br>

        <label for="student_gender">Gender:</label>
        <select name="student_gender" required>
            <option value="Male" <?php echo ($student['student_gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
            <option value="Female" <?php echo ($student['student_gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
        </select><br>


        <label for="student_nationality">Nationality:</label>
        <input type="text" name="student_nationality" value="<?php echo htmlspecialchars($student['student_nationality']); ?>"><br>

        <label for="student_phone">Phone:</label>
        <input type="text" name="student_phone" value="<?php echo htmlspecialchars($student['student_phone']); ?>"><br>

        <label for="student_religion">Religion:</label>
        <input type="text" name="student_religion" value="<?php echo htmlspecialchars($student['student_religion']); ?>"><br>

        <label for="student_mother_tongue">Mother Tongue:</label>
        <input type="text" name="student_mother_tongue" value="<?php echo htmlspecialchars($student['student_mother_tongue']); ?>"><br>

        <label for="school_attended">School Attended:</label>
        <input type="text" name="school_attended" value="<?php echo htmlspecialchars($student['school_attended']); ?>"><br>

        <label for="school_address">School Address:</label>
        <input type="text" name="school_address" value="<?php echo htmlspecialchars($student['school_address']); ?>"><br>

        <label for="school_medium">School Medium:</label>
        <input type="text" name="school_medium" value="<?php echo htmlspecialchars($student['school_medium']); ?>"><br>

        <label for="second_language">Second Language:</label>
        <input type="text" name="second_language" value="<?php echo htmlspecialchars($student['second_language']); ?>"><br>

        <label for="grade_passed">Grade Passed:</label>
        <input type="text" name="grade_passed" value="<?php echo htmlspecialchars($student['grade_passed']); ?>"><br>

        <label for="last_attended">Last Attended Date:</label>
        <input type="date" name="last_attended" value="<?php echo htmlspecialchars($student['last_attended']); ?>"><br>

        <label for="duration_of_stay">Duration of Stay:</label>
        <input type="text" name="duration_of_stay" value="<?php echo htmlspecialchars($student['duration_of_stay']); ?>"><br>

        <label for="reason_for_leaving">Reason for Leaving:</label>
        <input type="text" name="reason_for_leaving" value="<?php echo htmlspecialchars($student['reason_for_leaving']); ?>"><br>

        <label for="special_attention">Special Attention:</label>
        <input type="text" name="special_attention" value="<?php echo htmlspecialchars($student['special_attention']); ?>"><br>

        <!-- Father's Information -->
        <label for="father_name">Father's Name:</label>
        <input type="text" name="father_name" value="<?php echo htmlspecialchars($student['father_name']); ?>"><br>

        <label for="father_dob">Father's Date of Birth:</label>
        <input type="date" name="father_dob" value="<?php echo htmlspecialchars($student['father_dob']); ?>"><br>

        <label for="father_occupation">Father's Occupation:</label>
        <input type="text" name="father_occupation" value="<?php echo htmlspecialchars($student['father_occupation']); ?>"><br>

        <label for="father_mobile">Father's Mobile:</label>
        <input type="text" name="father_mobile" value="<?php echo htmlspecialchars($student['father_mobile']); ?>"><br>

        <label for="father_email">Father's Email:</label>
        <input type="email" name="father_email" value="<?php echo htmlspecialchars($student['father_email']); ?>"><br>

        <label for="father_school">Father's School Attended:</label>
        <input type="text" name="father_school" value="<?php echo htmlspecialchars($student['father_school']); ?>"><br>

        <label for="father_education">Father's Education:</label>
        <input type="text" name="father_education" value="<?php echo htmlspecialchars($student['father_education']); ?>"><br>

        <label for="father_residence">Father's Residence:</label>
        <input type="text" name="father_residence" value="<?php echo htmlspecialchars($student['father_residence']); ?>"><br>

        <!-- Mother's Information -->
        <label for="mother_name">Mother's Name:</label>
        <input type="text" name="mother_name" value="<?php echo htmlspecialchars($student['mother_name']); ?>"><br>

        <label for="mother_dob">Mother's Date of Birth:</label>
        <input type="date" name="mother_dob" value="<?php echo htmlspecialchars($student['mother_dob']); ?>"><br>

        <label for="mother_occupation">Mother's Occupation:</label>
        <input type="text" name="mother_occupation" value="<?php echo htmlspecialchars($student['mother_occupation']); ?>"><br>

        <label for="mother_mobile">Mother's Mobile:</label>
        <input type="text" name="mother_mobile" value="<?php echo htmlspecialchars($student['mother_mobile']); ?>"><br>

        <label for="mother_email">Mother's Email:</label>
        <input type="email" name="mother_email" value="<?php echo htmlspecialchars($student['mother_email']); ?>"><br>

        <label for="mother_school">Mother's School Attended:</label>
        <input type="text" name="mother_school" value="<?php echo htmlspecialchars($student['mother_school']); ?>"><br>

        <label for="mother_education">Mother's Education:</label>
        <input type="text" name="mother_education" value="<?php echo htmlspecialchars($student['mother_education']); ?>"><br>

        <label for="mother_residence">Mother's Residence:</label>
        <input type="text" name="mother_residence" value="<?php echo htmlspecialchars($student['mother_residence']); ?>"><br>

        <!-- Guardian Information -->
        <label for="guardian_name">Guardian's Name:</label>
        <input type="text" name="guardian_name" value="<?php echo htmlspecialchars($student['guardian_name']); ?>"><br>

        <label for="guardian_relationship">Guardian's Relationship:</label>
        <input type="text" name="guardian_relationship" value="<?php echo htmlspecialchars($student['guardian_relationship']); ?>"><br>

        <label for="guardian_mobile">Guardian's Mobile:</label>
        <input type="text" name="guardian_mobile" value="<?php echo htmlspecialchars($student['guardian_mobile']); ?>"><br>

        <label for="guardian_email">Guardian's Email:</label>
        <input type="email" name="guardian_email" value="<?php echo htmlspecialchars($student['guardian_email']); ?>"><br>

        <label for="guardian_residence">Guardian's Residence:</label>
        <input type="text" name="guardian_residence" value="<?php echo htmlspecialchars($student['guardian_residence']); ?>"><br>

        <!-- Siblings Information -->
<div class="section">
    <h3>Siblings</h3>

    <!-- Hidden input to store deleted sibling IDs -->
    <input type="hidden" id="deleted_siblings" name="deleted_siblings" value="[]">

    <?php
    // Loop through existing siblings to pre-fill the fields
    foreach ($siblings as $index => $sibling) {
    ?>
        <div class="siblings-group">
            <!-- Hidden input for sibling ID -->
            <input type="hidden" name="siblings[<?php echo $index; ?>][id]" value="<?php echo htmlspecialchars($sibling['id']); ?>">

            <label for="sibling_name_<?php echo $index; ?>">Sibling Name:</label>
            <input type="text" name="siblings[<?php echo $index; ?>][name]" value="<?php echo htmlspecialchars($sibling['name']); ?>" required><br>

            <label for="sibling_gender_<?php echo $index; ?>">Gender:</label>
            <select name="siblings[<?php echo $index; ?>][gender]" required>
                <option value="Male" <?php echo ($sibling['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo ($sibling['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Other" <?php echo ($sibling['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select><br>

            <label for="sibling_dob_<?php echo $index; ?>">Date of Birth:</label>
            <input type="date" name="siblings[<?php echo $index; ?>][dob]" value="<?php echo htmlspecialchars($sibling['dob']); ?>"><br>

            <label for="sibling_school_<?php echo $index; ?>">School:</label>
            <input type="text" name="siblings[<?php echo $index; ?>][school]" value="<?php echo htmlspecialchars($sibling['school']); ?>"><br>

            <label for="sibling_grade_<?php echo $index; ?>">Grade:</label>
            <input type="text" name="siblings[<?php echo $index; ?>][grade]" value="<?php echo htmlspecialchars($sibling['grade']); ?>"><br>

            <button type="button" class="remove-sibling" data-index="<?php echo $index; ?>">Remove Sibling</button>
            <br><br>
        </div>
    <?php
    }
    ?>

    <!-- Button to add more sibling input fields -->
    <button type="button" id="addSibling">Add Sibling</button>
</div>



        <!-- Emergency Contact Information -->
        <label for="emergency_name">Emergency Contact Name:</label>
        <input type="text" name="emergency_name" value="<?php echo htmlspecialchars($student['emergency_name']); ?>"><br>

        <label for="emergency_relationship">Emergency Contact Relationship:</label>
        <input type="text" name="emergency_relationship" value="<?php echo htmlspecialchars($student['emergency_relationship']); ?>"><br>

        <label for="emergency_mobile">Emergency Contact Mobile:</label>
        <input type="text" name="emergency_mobile" value="<?php echo htmlspecialchars($student['emergency_mobile']); ?>"><br>

        <label for="emergency_residence">Emergency Contact Residence:</label>
        <input type="text" name="emergency_residence" value="<?php echo htmlspecialchars($student['emergency_residence']); ?>"><br>

        <label for="emergency_office">Emergency Contact Office:</label>
        <input type="text" name="emergency_office" value="<?php echo htmlspecialchars($student['emergency_office']); ?>"><br>

        <label for="emergency_fax">Emergency Contact Fax:</label>
        <input type="text" name="emergency_fax" value="<?php echo htmlspecialchars($student['emergency_fax']); ?>"><br>

        <!-- Submit button -->
        <button type="submit">Update Profile</button>
    </form>
</body>
<script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('image-preview');
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        document.getElementById("addSibling").addEventListener("click", function () {
            var siblingCount = document.querySelectorAll(".siblings-group").length;
            var siblingGroup = document.createElement("div");
            siblingGroup.classList.add("siblings-group");

            siblingGroup.innerHTML = `
                <label for="sibling_name_${siblingCount}">Sibling Name:</label>
                <input type="text" name="siblings[${siblingCount}][name]" required><br>

                <label for="sibling_gender_${siblingCount}">Gender:</label>
                <select name="siblings[${siblingCount}][gender]" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select><br>

                <label for="sibling_dob_${siblingCount}">Date of Birth:</label>
                <input type="date" name="siblings[${siblingCount}][dob]"><br>

                <label for="sibling_school_${siblingCount}">School:</label>
                <input type="text" name="siblings[${siblingCount}][school]"><br>

                <label for="sibling_grade_${siblingCount}">Grade:</label>
                <input type="text" name="siblings[${siblingCount}][grade]"><br>

                <button type="button" class="remove-sibling" data-index="${siblingCount}">Remove Sibling</button>
            `;
            document.querySelector(".section").appendChild(siblingGroup);
        });

        document.addEventListener("click", function (e) {
            if (e.target && e.target.classList.contains("remove-sibling")) {
                var siblingGroup = e.target.closest(".siblings-group");
                var siblingId = siblingGroup.querySelector("input[name*='[id]']").value; // Get sibling ID if it exists

                if (siblingId) {
                    // Store the ID of the sibling to be deleted
                    var deletedSiblingsInput = document.getElementById("deleted_siblings");
                    var deletedSiblings = JSON.parse(deletedSiblingsInput.value || "[]");
                    deletedSiblings.push(siblingId);
                    deletedSiblingsInput.value = JSON.stringify(deletedSiblings);
                }

                siblingGroup.remove(); // Remove the sibling div from the UI
            }
        });


    </script>
</html>
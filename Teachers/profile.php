<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}
// Fetch the username from the session
$username = $_SESSION['user']['username'];

// Include the correct database connection file (make sure it's the right one)
include("db_connection.php");  

// Initialize variables
$errors = [];
$success = '';
$user_details = null;
$ol_results = [];
$al_results = [];
$teaching_classes = [];

// Function to sanitize input data (for future use in forms)
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Fetch user details
$sql_user = "
    SELECT 
        t.*, 
        u.active, 
        s.subject_name, 
        g.grade_name,
        ed.other_educational_qualification, 
        ed.professional_qualification, 
        ed.extra_curricular_activities, 
        ed.work_experience 
    FROM teacher t
    LEFT JOIN user u ON t.username = u.username
    LEFT JOIN subjects s ON t.subject_id = s.id
    LEFT JOIN grades g ON t.grade_id = g.id
    LEFT JOIN educational_details ed ON t.username = ed.username
    WHERE t.username = ?";
    
// Using PDO query here
$stmt = $pdo->prepare($sql_user);
$stmt->execute([$username]);  // Using $username for the logged-in teacher
$user_details = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if teacher exists
if ($user_details) {
    // Decode teaching_classes JSON to get class IDs
    $teaching_classes_ids = [];
    if (!empty($user_details['teaching_classes'])) {
        $teaching_classes_ids = json_decode($user_details['teaching_classes'], true);
    }

    // Fetch grade names for teaching_classes
    if (!empty($teaching_classes_ids)) {
        $placeholders = implode(',', array_fill(0, count($teaching_classes_ids), '?'));
        $sql_grades = "SELECT grade_name FROM grades WHERE id IN ($placeholders)";
        $stmt = $pdo->prepare($sql_grades);

        // Bind the grade IDs dynamically
        $stmt->execute($teaching_classes_ids);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $teaching_classes[] = $row['grade_name'];
        }
    }

    // Fetch GCE O/L Results
    $sql_ol_results = "
        SELECT subject_name, result 
        FROM ol_result_teacher 
        WHERE username = ?";
    $stmt = $pdo->prepare($sql_ol_results);
    $stmt->execute([$username]);
    $ol_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch GCE A/L Results
    $sql_al_results = "
        SELECT subject_name, result 
        FROM al_result_teacher 
        WHERE username = ?";
    $stmt = $pdo->prepare($sql_al_results);
    $stmt->execute([$username]);
    $al_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch Previous Experience
    $sql_experience = "
        SELECT previous_role, previous_company, years_experience 
        FROM previous_info 
        WHERE username = ?";
    $stmt = $pdo->prepare($sql_experience);
    $stmt->execute([$username]);
    $previous_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Handle the case where the teacher does not exist
    echo "Teacher details not found.";
}

$pdo = null;  // Close the PDO connection (or $conn if using mysqli)
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #eef2f3;
            margin: 0;
            padding: 20px;
        }
        .profile_container {
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        table th {
            background-color: #3498db;
            color: #fff;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #777;
            margin-top: 10px;
        }
        @media (max-width: 600px) {
            .detail-item {
                width: 100%;
            }
            .profile-image {
                width: 150px;
                height: 150px;
            }
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
        }
        .back-link a {
            text-decoration: none;
            color: #3498db;
            font-weight: bold;
        }
        .back-link a:hover {
            text-decoration: underline;
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
        /* Buttons Styling */
        .buttons {
            position: absolute;
            top: 30px;
            right: 30px;
        }
        .buttons button, .buttons a {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 10px 15px;
            margin-left: 10px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .buttons button:hover, .buttons a:hover {
            background-color: #2980b9;
        }
       

    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="profile_container">

        <div class="buttons no-print">
            <a href="./edit_teacher.php">Edit</a>
        </div>

        <?php if ($user_details): ?>
            <h2><?php echo htmlspecialchars($user_details['full_name']); ?>'s Profile</h2>

            <div class="profile-header">
                <?php 
                // Ensure the correct path without duplicating "uploads/"
                $profile_image = $user_details['profile_image']; 
                if (strpos($profile_image, 'uploads/') === 0) {
                    // Remove redundant "uploads/" if present
                    $profile_image = substr($profile_image, strlen('uploads/'));
                }

                // Construct the correct paths
                $profile_image_path = "../uploads/" . $profile_image;
                $absolute_file_path = __DIR__ . '/../uploads/' . $profile_image;

                if (!empty($user_details['profile_image']) && file_exists($absolute_file_path)): ?>
                    <img src="<?= htmlspecialchars($profile_image_path) ?>" alt="Profile Image" style="width: 150px; height: 150px; border-radius: 50%;">
                <?php else: ?>
                    <p>No profile image available.</p>
                    <p>Path checked: <?= htmlspecialchars($absolute_file_path) ?></p>
                <?php endif; ?>
            </div>

            <div class="details">
                <div class="detail-item"><label>Full Name:</label><span><?php echo htmlspecialchars($user_details['full_name']); ?></span></div>
                <div class="detail-item"><label>Username:</label><span><?php echo htmlspecialchars($user_details['username']); ?></span></div>
                <div class="detail-item"><label>Gender:</label><span><?php echo htmlspecialchars($user_details['gender']); ?></span></div>
                <div class="detail-item"><label>Date of Birth:</label><span><?php echo htmlspecialchars($user_details['date_of_birth']); ?></span></div>
                <div class="detail-item"><label>Address:</label><span><?php echo htmlspecialchars($user_details['postal_address']); ?></span></div>
                <div class="detail-item"><label>Ethnicity:</label><span><?php echo htmlspecialchars($user_details['ethnicity']); ?></span></div>
                <div class="detail-item"><label>NIC Number:</label><span><?php echo htmlspecialchars($user_details['nic_number']); ?></span></div>
                <div class="detail-item"><label>Marital Status:</label><span><?php echo htmlspecialchars($user_details['marital_status']); ?></span></div>
                <div class="detail-item"><label>WhatsApp Number:</label><span><?php echo htmlspecialchars($user_details['whatsapp_number']); ?></span></div>
                <div class="detail-item"><label>Residence Number:</label><span><?php echo htmlspecialchars($user_details['residence_number']); ?></span></div>
                <div class="detail-item"><label>First Language:</label><span><?php echo htmlspecialchars($user_details['first_language']); ?></span></div>
                <div class="detail-item"><label>Assigned Class:</label><span><?php echo htmlspecialchars($user_details['grade_name'] ?? 'N/A'); ?></span></div>
                <div class="detail-item"><label>Assigned Subject:</label><span><?php echo htmlspecialchars($user_details['subject_name'] ?? 'N/A'); ?></span></div>
                <div class="detail-item"><label>Rank:</label><span><?php echo htmlspecialchars($user_details['rank'] ?? 'N/A'); ?></span></div>
            </div>

            <h3>Teaching Classes</h3>
            <div class="section">
                <?php if (!empty($teaching_classes)): ?>
                    <ul>
                        <?php foreach ($teaching_classes as $class): ?>
                            <li><?php echo htmlspecialchars($class); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-data">No classes assigned.</p>
                <?php endif; ?>
            </div>



            <!-- Education -->
            <h3>Educational Qualifications</h3>
            <div class="detail-item"><label>Other Qualifications:</label><span><?php echo htmlspecialchars($user_details['other_educational_qualification'] ?? 'N/A'); ?></span></div>
            <div class="detail-item"><label>Professional Qualifications:</label><span><?php echo htmlspecialchars($user_details['professional_qualification'] ?? 'N/A'); ?></span></div>
            <div class="detail-item"><label>Extra Curricular Activities:</label><span><?php echo htmlspecialchars($user_details['extra_curricular_activities'] ?? 'N/A'); ?></span></div>
            <div class="detail-item"><label>Work Experience:</label><span><?php echo htmlspecialchars($user_details['work_experience'] ?? 'N/A'); ?></span></div>

            <h3>Previous Work Experience</h3>
            <?php if ($previous_info): ?>
                <ul>
                    <?php foreach ($previous_info as $info): ?>
                        <li><?php echo htmlspecialchars($info['previous_role']); ?> at <?php echo htmlspecialchars($info['previous_company']); ?> (<?php echo htmlspecialchars($info['years_experience']); ?> years)</li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>

                <p>No Previous Experience Found</p>
            <?php endif; ?>

            <!-- O/L Results -->
            <h3>GCE O/L Results</h3>
            <?php if ($ol_results): ?>
                <table><thead><tr><th>Subject</th><th>Result</th></tr></thead><tbody>
                    <?php foreach ($ol_results as $ol): ?>
                        <tr><td><?php echo htmlspecialchars($ol['subject_name']); ?></td><td><?php echo htmlspecialchars($ol['result']); ?></td></tr>
                    <?php endforeach; ?>
                </tbody></table>
            <?php else: ?>
                <p>No O/L Results Found</p>

            <?php endif; ?>
            <!-- A/L Results -->
            <h3>GCE A/L Results</h3>
            <?php if ($al_results): ?>
                <table><thead><tr><th>Subject</th><th>Result</th></tr></thead><tbody>
                    <?php foreach ($al_results as $al): ?>
                        <tr><td><?php echo htmlspecialchars($al['subject_name']); ?></td><td><?php echo htmlspecialchars($al['result']); ?></td></tr>
                    <?php endforeach; ?>
                </tbody></table>
            <?php else: ?>

                <p>No A/L Results Found</p>
            <?php endif; ?>
            <!-- Previous Experience -->
            
        <?php else: ?>
            <p>User not found.</p>
        <?php endif; ?>
    </div>



</body>
</html>

<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'database_connection.php';

// Get username filter from query parameter (e.g., username passed in URL)
$username_filter = isset($_GET['username']) ? $_GET['username'] : '';

// Fetch staff details
$query = "
    SELECT staff.*, user.*, educational_details.other_educational_qualification, educational_details.professional_qualification,
           educational_details.extra_curricular_activities, educational_details.work_experience, staff.position 
    FROM staff 
    LEFT JOIN user ON staff.username = user.username
    LEFT JOIN educational_details ON staff.username = educational_details.username
    WHERE staff.username = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username_filter);
$stmt->execute();
$user_result = $stmt->get_result();
$user_details = $user_result->fetch_assoc();

// Fetch previous experience
$experience_query = "SELECT * FROM previous_info WHERE username = ?";
$exp_stmt = $conn->prepare($experience_query);
$exp_stmt->bind_param("s", $username_filter);
$exp_stmt->execute();
$previous_info = $exp_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch GCE O/L Results
$ol_query = "SELECT * FROM ol_result_staff WHERE username = ?";
$ol_stmt = $conn->prepare($ol_query);
$ol_stmt->bind_param("s", $username_filter);
$ol_stmt->execute();
$ol_results = $ol_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch GCE A/L Results
$al_query = "SELECT * FROM al_result_staff WHERE username = ?";
$al_stmt = $conn->prepare($al_query);
$al_stmt->bind_param("s", $username_filter);
$al_stmt->execute();
$al_results = $al_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['block']) && !empty($_POST['username']) && !empty($_POST['block_reason'])) {
        $username = $_POST['username'];
        $block_reason = $_POST['block_reason'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update user table to set active = 0
            $sql_block = "UPDATE user SET active = 0 WHERE username = ?";
            $stmt = $conn->prepare($sql_block);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();

            // Insert the block reason into block_reasons table
            $sql_reason = "INSERT INTO block_reasons (username, block_reason) VALUES (?, ?)";
            $stmt = $conn->prepare($sql_reason);
            $stmt->bind_param("ss", $username, $block_reason);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            echo "<script>
                    alert('User has been blocked successfully.');
                    window.location.href = 'blocks.php'; // Redirect to a page, e.g., user list
                  </script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p>Error blocking user: " . $e->getMessage() . "</p>";
        }
    }

    if (isset($_POST['activate']) && !empty($_POST['username'])) {
        $username = $_POST['username'];

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update user table to set active = 1
            $sql_activate = "UPDATE user SET active = 1 WHERE username = ?";
            $stmt = $conn->prepare($sql_activate);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();

            // Delete the block reason from block_reasons table
            $sql_delete_reason = "DELETE FROM block_reasons WHERE username = ?";
            $stmt = $conn->prepare($sql_delete_reason);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            echo "<script>
                    alert('User has been Unblocked successfully.');
                    window.location.href = 'view_teachers_staff.php'; // Redirect to a page, e.g., user list
                  </script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<p>Error activating user: " . $e->getMessage() . "</p>";
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Profile</title>
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
        /* Delete Button Styling */
        .buttons .delete-btn {
            background-color: #e74c3c;
        }
        .buttons .delete-btn:hover {
            background-color: #c0392b;
        }
        /* Delete Button Styling */
        .buttons .delete-btn {
            background-color: #e74c3c;
        }
        .buttons .delete-btn:hover {
            background-color: #c0392b;
        }

        .block-btn {
            background-color: #f39c12; /* Orange */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .block-btn:hover {
            background-color: #e67e22; /* Darker orange */
        }

        .active-btn {
            background-color: #27ae60; /* Green */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .active-btn:hover {
            background-color: #2ecc71; /* Lighter green */
        }
        /* Modal Background Overlay */
    #blockModalOverlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    /* Modal Container */
    #blockModal {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        width: 90%;
        max-width: 400px;
    }

    /* Modal Form */
    #blockModal form {
        display: flex;
        flex-direction: column;
    }

    #blockModal textarea {
        margin-bottom: 15px;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        resize: none;
    }

    #blockModal button {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
        font-size: 14px;
    }

    #blockModal button[type="submit"] {
        background-color: #e74c3c;
        color: #fff;
    }

    #blockModal button[type="button"] {
        background-color: #3498db;
        color: #fff;
    }

    #blockModal button[type="submit"]:hover {
        background-color: #c0392b;
    }

    #blockModal button[type="button"]:hover {
        background-color: #2980b9;
    }


    </style>
</head>
<body>
    <div class="container">
        <!-- Buttons -->
        <div class="buttons no-print">
            <button onclick="window.print()">Print</button>
            <a href="./edit_staff_profile.php?username=<?php echo urlencode($username_filter); ?>">Edit</a>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this profile?');" style="display: inline;">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username_filter); ?>">
                <button type="submit" name="delete" class="delete-btn">Delete</button>
            </form>

            <?php if ($user_details['active'] == 1): ?>
                <!-- Block Button -->
                <button onclick="openBlockModal('<?php echo htmlspecialchars($username_filter); ?>')" class="block-btn">Block</button>
            <?php else: ?>
                <!-- Activate Button -->
                <form method="POST" onsubmit="return confirm('Are you sure you want to activate this user?');" style="display: inline;">
                    <input type="hidden" name="username" value="<?php echo htmlspecialchars($username_filter); ?>">
                    <button type="submit" name="activate" class="active-btn">Activate</button>
                </form>
            <?php endif; ?>

        </div>

        <?php if ($user_details): ?>
            <h2><?php echo htmlspecialchars($user_details['full_name']); ?>'s Profile</h2>

            <!-- Profile Header (Image) -->
            <div class="profile-header">
                <?php
                $profile_image_path = 'uploads/' . htmlspecialchars($user_details['profile_image']);
                if (!empty($user_details['profile_image']) && file_exists($profile_image_path)): ?>
                    <img class="profile-image" src="<?php echo $profile_image_path; ?>" alt="Profile Image">
                <?php else: ?>
                    <img class="profile-image" src="./Resources/default_profile.png" alt="No Image">
                <?php endif; ?>
            </div>

            <!-- Personal Details -->
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
                <div class="detail-item"><label>Position:</label><span><?php echo htmlspecialchars($user_details['position']); ?></span></div>
            </div>

            <!-- Educational Details -->
            <h3>Educational Qualifications</h3>
            <div class="detail-item"><label>Other Qualifications:</label><span><?php echo htmlspecialchars($user_details['other_educational_qualification'] ?? 'N/A'); ?></span></div>
            <div class="detail-item"><label>Professional Qualifications:</label><span><?php echo htmlspecialchars($user_details['professional_qualification'] ?? 'N/A'); ?></span></div>
            <div class="detail-item"><label>Extra Curricular Activities:</label><span><?php echo htmlspecialchars($user_details['extra_curricular_activities'] ?? 'N/A'); ?></span></div>
            <div class="detail-item"><label>Work Experience:</label><span><?php echo htmlspecialchars($user_details['work_experience'] ?? 'N/A'); ?></span></div>

            <!-- Previous Work Experience -->
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

            <!-- GCE O/L Results -->
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

            <!-- GCE A/L Results -->
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

        <?php else: ?>
            <p>Staff profile not found.</p>
        <?php endif; ?>

        <div id="blockModalOverlay" onclick="closeBlockModal()"></div>
        <div id="blockModal">
            <form method="POST" id="blockForm">
                <input type="hidden" name="username" id="blockUsername">
                <label for="blockReason">Reason for Blocking:</label>
                <textarea name="block_reason" id="blockReason" rows="4" required></textarea>
                <button type="submit" name="block">Submit</button>
                <button type="button" onclick="closeBlockModal()">Cancel</button>
            </form>
        </div>
   

</body>
<script>
    function openBlockModal(username) {
        document.getElementById('blockUsername').value = username;
        document.getElementById('blockModalOverlay').style.display = 'block';
        document.getElementById('blockModal').style.display = 'block';
    }

    function closeBlockModal() {
        document.getElementById('blockModalOverlay').style.display = 'none';
        document.getElementById('blockModal').style.display = 'none';
    }
    </script>

</html>
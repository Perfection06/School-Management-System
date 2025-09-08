<?php
session_start();
include('database_connection.php'); // Include database connection

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Get admin username from session
$username = htmlspecialchars($_SESSION['user']['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcast Messaging</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* General Reset */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        /* Container Styling */
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.8em;
            color: #0056b3;
        }

        /* Form Styling */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        select, textarea, input[type="file"], button {
            width: 100%;
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        select, textarea {
            background-color: #fff;
        }

        textarea {
            resize: none;
        }

        input[type="file"] {
            padding: 5px;
        }

        button {
            background-color: #0056b3;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
            border: none;
        }

        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        button:hover:not(:disabled) {
            background-color: #004494;
        }


        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            h1 {
                font-size: 1.5em;
            }
        }
    </style>
    
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="container">
        <h1>Broadcast Messaging</h1>
        <form action="send_broadcast.php" method="POST" enctype="multipart/form-data">
            <!-- Recipient Type Selection -->
            <label for="recipientType">Select Recipient Type:</label>
            <select name="recipientType" id="recipientType" onchange="toggleGradeSelection()" required>
                <option value="">--Select--</option>
                <option value="Students">Students</option>
                <option value="Teachers">Teachers</option>
                <option value="NoClass_Teachers">No-Class Teachers</option>
                <option value="Staff">Staff</option>
            </select>


            <!-- Grade Selection (for Students Only) -->
            <div id="gradeSection" style="display:none;">
                <label for="grade">Select Grade:</label>
                <select name="grade" id="grade">
                    <option value="">--Select Grade--</option>
                    <?php
                    // Fetch grades from the database
                    $grades_query = "SELECT id, grade_name FROM grades";
                    $grades_result = $conn->query($grades_query);
                    while ($row = $grades_result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['grade_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Message Content -->
            <label for="message">Message:</label>
            <textarea name="message" id="message" rows="5" required></textarea>

            <!-- File Attachment -->
            <label for="attachment">Attach File (optional):</label>
            <input type="file" name="attachment" id="attachment">

            <!-- Submit Button -->
            <button type="submit" class="btn" id="submitBtn" disabled>Send Broadcast</button>
        </form>
    </div>
</body>
<script>
        // Enable or disable grade selection based on recipient type
        function toggleGradeSelection() {
            const recipientType = document.getElementById('recipientType').value;
            const gradeSection = document.getElementById('gradeSection');
            const gradeSelect = document.getElementById('grade');
            const submitBtn = document.getElementById('submitBtn');

            if (recipientType === 'Students') {
                gradeSection.style.display = 'block';
                gradeSelect.required = true;
            } else {
                gradeSection.style.display = 'none';
                gradeSelect.required = false;
                gradeSelect.value = ""; // Reset grade selection
            }

            // Enable the submit button if recipient type is selected
            submitBtn.disabled = !recipientType;
        }
    </script>
</html>
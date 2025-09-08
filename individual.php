<?php
session_start();
include('database_connection.php'); 

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Get admin username from session
$username = htmlspecialchars($_SESSION['user']['username']);

// Fetch grades for students
$grades_query = "SELECT * FROM grades";
$grades_result = mysqli_query($conn, $grades_query);

// Fetch users based on role
$role_query = "SELECT username, role FROM user WHERE active = 1";
$role_result = mysqli_query($conn, $role_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Messaging</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 500px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #4A89DC;
            border: none;
        }

        .btn-primary:hover {
            background-color: #3C6382;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container">
    <h2 class="text-center text-primary mb-4">Send Individual Message</h2>

    <form action="send_message.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="recipientType" class="form-label">Select Recipient Type:</label>
            <select class="form-select" name="recipientType" id="recipientType" onchange="toggleRecipientOptions()">
                <option value="">--Select--</option>
                <option value="students">Students</option>
                <option value="teachers">Teachers</option>
                <option value="noClassTeachers">NoClass Teachers</option>
                <option value="staff">Staff</option>
            </select>
        </div>

        <div id="gradeSection" class="mb-3" style="display:none;">
            <label for="grade" class="form-label">Select Grade:</label>
            <select class="form-select" name="grade" id="grade" onchange="fetchUsers('students')">
                <option value="">--Select Grade--</option>
                <?php while ($row = mysqli_fetch_assoc($grades_result)) { ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['grade_name']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div id="userSection" class="mb-3" style="display:none;">
            <label class="form-label">Select Users:</label>
            <div class="form-check" id="userCheckboxes"></div>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message:</label>
            <textarea class="form-control" name="message" id="message" rows="4" required></textarea>
        </div>

        <div class="mb-3">
            <label for="attachment" class="form-label">Attach File (optional):</label>
            <input type="file" class="form-control" name="attachment" id="attachment">
        </div>

        <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>Send Message</button>
    </form>
</div>


<script>
    function toggleRecipientOptions() {
        const recipientType = document.getElementById("recipientType").value;
        const gradeSection = document.getElementById("gradeSection");
        const userSection = document.getElementById("userSection");
        const userCheckboxes = document.getElementById("userCheckboxes");
        const submitBtn = document.getElementById("submitBtn");

        gradeSection.style.display = 'none';
        userSection.style.display = 'none';
        userCheckboxes.innerHTML = '';

        if (recipientType === "students") {
            gradeSection.style.display = 'block';
        } else if (["teachers", "noClassTeachers", "staff"].includes(recipientType)) {
            userSection.style.display = 'block';
            fetchUsers(recipientType);
        }

        submitBtn.disabled = !recipientType;
    }

    document.getElementById("grade").addEventListener("change", function () {
        const recipientType = document.getElementById("recipientType").value;
        if (recipientType === "students") {
            fetchUsers("students");
        }
    });

    function fetchUsers(type) {
        const gradeId = document.getElementById("grade").value;

        if (type === "students" && !gradeId) {
            console.log('Grade ID is missing for students.');
            return;
        }

        console.log(`Fetching users for type: ${type}, grade_id: ${gradeId}`);

        const xhr = new XMLHttpRequest();
        let url = `fetch_user.php?type=${type}`;
        if (type === "students") {
            url += `&grade_id=${gradeId}`;
        }

        xhr.open('GET', url, true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                const users = JSON.parse(xhr.responseText);
                let checkboxesHTML = '';
                if (users.error) {
                    checkboxesHTML = `<p class="text-danger">${users.error}</p>`;
                } else {
                    users.forEach(user => {
                        checkboxesHTML += `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="users[]" value="${user.username}" id="user_${user.username}">
                                <label class="form-check-label" for="user_${user.username}">
                                    ${user.name} (${user.username})
                                </label>
                            </div>
                        `;
                    });
                }
                document.getElementById("userCheckboxes").innerHTML = checkboxesHTML;
                document.getElementById("userSection").style.display = users.length ? 'block' : 'none';
            } else {
                console.error('Error fetching users: ' + xhr.statusText);
            }
        };
        xhr.send();
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

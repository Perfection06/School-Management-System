<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

include('database_connection.php');

$username = $_SESSION['user']['username'];

// Get the student's grade
$grade_query = "SELECT grade_id FROM Students WHERE username = ?";
$stmt = $conn->prepare($grade_query);
$stmt->bind_param('s', $username);
$stmt->execute();
$grade_result = $stmt->get_result();
$grade_row = $grade_result->fetch_assoc();
$grade_id = $grade_row['grade_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    $attachment_path = null;

    if ($content === '') {
        echo "<script>alert('Message content cannot be empty.');</script>";
        exit;
    }

    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $file_type = mime_content_type($_FILES['attachment']['tmp_name']);
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_name = basename($_FILES['attachment']['name']);
            $target_file = $upload_dir . time() . '_' . $file_name;
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
                $attachment_path = $target_file;
            } else {
                echo "<script>alert('Failed to upload attachment.');</script>";
                exit;
            }
        } else {
            echo "<script>alert('Invalid file type. Allowed types: JPG, PNG, PDF, DOC, DOCX.');</script>";
            exit;
        }
    }

    $receiver_username = 'admin'; // Fixed recipient for student messages

    $stmt = $conn->prepare("INSERT INTO Student_Messages (sender_username, receiver_username, content, attachment_path, grade_id) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('ssssi', $username, $receiver_username, $content, $attachment_path, $grade_id);
        if ($stmt->execute()) {
            echo "<script>alert('Message sent successfully!');</script>";
        } else {
            echo "<script>alert('Failed to send message.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Database error: " . $conn->error . "');</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message to Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            margin: 20px auto;
            max-width: 600px;
            background: #fff;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 8px;
            font-weight: bold;
        }
        input[type="file"], textarea, button {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
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
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="container">
        <h1>Send Message to Admin</h1>
        <form action="message.php" method="POST" enctype="multipart/form-data">
            <label for="content">Message Content</label>
            <textarea name="content" id="content" rows="5" required></textarea>

            <label for="attachment">Attachment (Optional)</label>
            <input type="file" name="attachment" id="attachment" accept=".jpg,.png,.pdf,.doc,.docx">

            <button type="submit">Send Message</button>
        </form>
    </div>
</body>
</html>

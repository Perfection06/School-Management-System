<?php
include('database_connection.php'); // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_username = 'admin'; // Replace with actual admin's username from session
    $recipient_type = $_POST['recipientType'];
    $message_content = $_POST['message'];
    $grade_id = $_POST['grade'] ?? null; // Grade ID for students (if applicable)
    $attachment_path = '';

    // Directly map recipient type to target group
    $target_group = $recipient_type;

    // Validate form inputs
    if (!$target_group) {
        echo "<script>alert('Invalid recipient type!'); window.history.back();</script>";
        exit();
    }

    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create uploads folder if it doesn't exist
        }

        $file_name = basename($_FILES['attachment']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $file_path)) {
            $attachment_path = $file_path;
        } else {
            echo "<script>alert('Failed to upload attachment.'); window.history.back();</script>";
            exit();
        }
    }

    // Insert broadcast message into the database
    $stmt = $conn->prepare("INSERT INTO messages 
        (sender_username, content, attachment_path, is_broadcast, target_group, grade_id)
        VALUES (?, ?, ?, 1, ?, ?)");

    // Use NULL for grade_id if not applicable
    $actual_grade_id = ($target_group === 'Students') ? $grade_id : null;

    $stmt->bind_param("ssssi", $sender_username, $message_content, $attachment_path, $target_group, $actual_grade_id);

    if ($stmt->execute()) {
        echo "<script>
            alert('Broadcast message sent successfully!');
            window.location.href = 'broadcast.php'; // Redirect after success
        </script>";
    } else {
        error_log("Error inserting broadcast message: " . $stmt->error);
        echo "<script>
            alert('Failed to send broadcast message.');
            window.history.back();
        </script>";
    }

    exit();
}
?>

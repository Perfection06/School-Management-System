<?php
include('database_connection.php'); // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $sender_username = 'admin'; // Replace with actual admin's username from session
    $recipient_type = $_POST['recipientType']; // Recipient type
    $message_content = $_POST['message']; // Message content
    $users = $_POST['users'] ?? []; // Selected users (checkboxes for individual messages)
    $grade_id = $_POST['grade'] ?? null; // Only applicable for students
    $attachment_path = '';

    // Map frontend recipient types to database ENUM values
    $recipient_type_mapping = [
        'students' => 'Students',
        'teachers' => 'Teachers',
        'noClassTeachers' => 'NoClass_Teachers',
        'staff' => 'Staff'
    ];
    $recipient_type_db = $recipient_type_mapping[$recipient_type] ?? null;

    // Handle file upload if present
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $upload_dir = 'uploads/';
        $file_name = basename($_FILES['attachment']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $file_path)) {
            $attachment_path = $file_path;
        }
    }

    // Sending Messages
    if (!empty($users)) {
        // Individual Messages
        foreach ($users as $receiver_username) {
            if ($recipient_type === 'students') {
                // Insert into student_send_messages table
                $stmt = $conn->prepare("INSERT INTO student_send_messages 
                    (sender_username, receiver_username, content, attachment_path, is_broadcast, grade_id)
                    VALUES (?, ?, ?, ?, 0, ?)");
                
                $stmt->bind_param("ssssi", $sender_username, $receiver_username, $message_content, $attachment_path, $grade_id);
            } else {
                // Insert into messages table for teachers, staff, etc.
                $stmt = $conn->prepare("INSERT INTO messages 
                    (sender_username, receiver_username, content, attachment_path, is_broadcast, target_group, grade_id)
                    VALUES (?, ?, ?, ?, 0, ?, ?)");
                
                // Set grade_id to NULL for non-student roles
                $actual_grade_id = null;

                $stmt->bind_param("sssssi", $sender_username, $receiver_username, $message_content, $attachment_path, $recipient_type_db, $actual_grade_id);
            }

            if (!$stmt->execute()) {
                error_log("Error inserting individual message: " . $stmt->error);
            }
        }
    } else {
        // Broadcast Messages
        if ($recipient_type === 'students') {
            // Broadcast message for students (Insert into student_send_messages)
            $stmt = $conn->prepare("INSERT INTO student_send_messages 
                (sender_username, content, attachment_path, is_broadcast, grade_id)
                VALUES (?, ?, ?, 1, ?)");
            
            $stmt->bind_param("sssi", $sender_username, $message_content, $attachment_path, $grade_id);
        } else {
            // Broadcast message for teachers, staff (Insert into messages)
            $stmt = $conn->prepare("INSERT INTO messages 
                (sender_username, content, attachment_path, is_broadcast, target_group, grade_id)
                VALUES (?, ?, ?, 1, ?, ?)");
            
            // Set grade_id to NULL for non-student roles
            $actual_grade_id = null;

            $stmt->bind_param("sssii", $sender_username, $message_content, $attachment_path, $recipient_type_db, $actual_grade_id);
        }

        if (!$stmt->execute()) {
            error_log("Error inserting broadcast message: " . $stmt->error);
        }
    }

    // Feedback and redirection
    echo "<script>
        alert('Messages sent successfully!');
        window.location.href = 'individual.php'; // Redirect after the alert
    </script>";
    exit();
}
?>

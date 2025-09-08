<?php
// Check if the email parameter is set
if (isset($_GET['email'])) {
    $email = htmlspecialchars($_GET['email']);
    $subject = "Student Admission";
    
    // Use plain text body content with clear paragraph breaks
    $body = "Hello Mr/Mrs,%0A%0A"
          . "Please click the link below to fill out the student admission%0A%0A"
          . "form: http://localhost/sms/add_student.php%0A%0A"
          . "Thank you!";

    // Gmail URL to open the compose window with pre-filled details
    $gmailURL = "https://mail.google.com/mail/?view=cm&fs=1&to=" . urlencode($email) 
              . "&su=" . urlencode($subject) 
              . "&body=" . $body; // No need to urlencode body again
    
    // Redirect to the Gmail compose window
    header("Location: $gmailURL");
    exit;
} else {
    echo "Error: Email address not provided.";
}
?>

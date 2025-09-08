<?php
// Database connection
include 'database_connection.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from the form
    $contact_number = $_POST['contact_number'];
    $reference_number = $_POST['reference_number'];

    // Generate the admission link (update the URL to your actual admission form)
    $admission_link = "http://localhost/SMS/add_student.php?ref=" . urlencode($reference_number);

    // Send SMS using Send.lk API
    $api_token = '2352|snbXixIY7jZ6dw3Z5CznvyFyE5uloU7QO3DrGWZN'; 
    $sms_url = 'https://sms.send.lk/api/v3/sms/send'; // Correct endpoint

    // Prepare POST data for Send.lk API
    $postData = [
        'to' => $contact_number,
        'message' => "Dear Parent/Guardian,\n\nYour admission link is: $admission_link\n\nThank you!"
    ];

    // Send POST request to Send.lk
    $options = [
        'http' => [
            'header' => [
                "Authorization: Bearer $api_token",
                "Content-Type: application/json"
            ],
            'method' => 'POST',
            'content' => json_encode($postData)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($sms_url, false, $context);
    $responseData = json_decode($response, true);

    // Check the response from the SMS API
    if (isset($responseData['status']) && $responseData['status'] == 'success') { // Check for success
        echo "Admission link sent successfully to $contact_number.";
    } else {
        echo "Failed to send admission link. Please try again.";
        echo "Response: " . json_encode($responseData); // Print the full response for debugging
    }
} else {
    echo "Invalid request method.";
}

mysqli_close($conn);
?>

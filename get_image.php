<?php
// Database connection
include('database_connection.php');

// Check if an ID is provided
if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // Fetch the BLOB data from the database
    $query = "SELECT profile_image FROM Students WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($profile_image);

    if ($stmt->fetch() && !empty($profile_image)) {
        // Output the image
        header("Content-Type: image/jpeg"); // Set appropriate image type
        echo $profile_image;
        exit;
    } else {
        // Default image if no BLOB is found
        header("Content-Type: image/png");
        readfile("default-image.png");
        exit;
    }
} else {
    echo "No ID provided.";
    exit;
}
?>

<?php
// Include database connection
include('database_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);

    // Delete the admission
    $delete_query = "DELETE FROM Student_Admissions WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: pending_admissions.php"); 
}
?>

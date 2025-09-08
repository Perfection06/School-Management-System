<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
include('database_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admission_id = intval($_POST['id']); // This is the admission ID now
    $assigning_grade = $_POST['assigning_grade'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Fetch the student name from the Student_Admissions table
        $name_query = "SELECT student_name FROM Student_Admissions WHERE id = ?";
        $name_stmt = $conn->prepare($name_query);
        $name_stmt->bind_param("i", $admission_id);
        $name_stmt->execute();
        $name_stmt->bind_result($student_name);
        $name_stmt->fetch();
        $name_stmt->close();

        // Check if the student name was found
        if (empty($student_name)) {
            throw new Exception("Student name not found for the given ID.");
        }

        // Update the admission status
        $update_query = "UPDATE Student_Admissions SET status = 'approved', assigning_grade = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $assigning_grade, $admission_id);
        $stmt->execute();

        // Add the student to the Students table, using the admission_id as the id
        $student_query = "INSERT INTO Students (id, name, username, password, grade_id) 
        VALUES (?, ?, ?, ?, ?)";
        $student_stmt = $conn->prepare($student_query);
        $student_stmt->bind_param("isssi", $admission_id, $student_name, $username, $password, $assigning_grade);
        $student_stmt->execute();


        // Commit the transaction
        $conn->commit();

        // Use JavaScript alert and redirection
    echo "<script>
    alert('Transaction completed successfully. Redirecting to pending admissions.');
    window.location.href = 'pending_admissions.php';
  </script>";
exit();
} catch (Exception $e) {
// Rollback the transaction in case of an error
$conn->rollback();
echo "<script>
    alert('Error occurred: " . addslashes($e->getMessage()) . "');
    window.history.back();
  </script>";
exit();
}}
?>


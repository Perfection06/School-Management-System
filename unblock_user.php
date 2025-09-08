<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once 'database_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role'];

    // Unblocking and deleting block reason based on role
    if ($role === 'Accountant') {
        // Unblock accountant
        $unblock_sql = "UPDATE accountant SET active = 1 WHERE username = ?";
        $delete_reason_sql = "DELETE FROM accountant_block_reasons WHERE username = ?";
    } else {
        // Unblock other users
        $unblock_sql = "UPDATE user SET active = 1 WHERE username = ?";
        $delete_reason_sql = "DELETE FROM block_reasons WHERE username = ?";
    }

    // Prepare and execute unblock SQL
    $stmt = $conn->prepare($unblock_sql);
    $stmt->bind_param('s', $username);
    $unblock_success = $stmt->execute();
    $stmt->close();

    // Prepare and execute delete block reason SQL
    $stmt = $conn->prepare($delete_reason_sql);
    $stmt->bind_param('s', $username);
    $delete_reason_success = $stmt->execute();
    $stmt->close();

    // Check if both operations were successful
    if ($unblock_success && $delete_reason_success) {
        $_SESSION['success_message'] = "User $username has been unblocked and their block reason removed.";
    } else {
        $_SESSION['error_message'] = "Failed to unblock user $username or remove their block reason.";
    }

    $conn->close();

    header("Location: blocks.php");
    exit;
}
?>

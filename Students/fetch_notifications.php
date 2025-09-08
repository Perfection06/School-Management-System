<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

include('db_connection.php');  // Assuming $pdo is initialized in this file

// Get the student's username and grade from the session
$username = htmlspecialchars($_SESSION['user']['username']);

// Fetch the student's grade ID using PDO
$query = "
    SELECT grade_id FROM Students WHERE username = :username
";
$stmt = $pdo->prepare($query);
$stmt->execute(['username' => $username]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// If student not found or no grade assigned, exit
if (!$student) {
    exit('Student not found.');
}

$grade_id = $student['grade_id'];

// Query to fetch tests for the student's grade with a future test date using PDO
$testQuery = "
    SELECT t.type, s.subject_name, t.test_date 
    FROM tests t
    JOIN subjects s ON t.subject_id = s.id
    WHERE t.grade_id = :grade_id AND t.test_date > NOW()
    ORDER BY t.test_date ASC
";

// Prepare and execute the query using PDO
$stmt = $pdo->prepare($testQuery);
$stmt->execute(['grade_id' => $grade_id]);

$notifications = [];

// If there are upcoming tests, add them to notifications
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = [
            'type' => 'Test',
            'message' => 'Upcoming ' . $row['type'] . ' for ' . $row['subject_name'] . ' on ' . date('F j, Y', strtotime($row['test_date'])),
        ];
    }
}

// Fetch upcoming events using PDO
$eventsQuery = "SELECT title, start_date FROM events WHERE start_date > NOW() ORDER BY start_date ASC";
$stmt = $pdo->prepare($eventsQuery);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = [
            'type' => 'Event',
            'message' => 'Upcoming event: ' . $row['title'] . ' starting on ' . date('F j, Y, g:i a', strtotime($row['start_date'])),
        ];
    }
}

// Fetch notices using PDO
$noticesQuery = "SELECT title FROM notices WHERE end_date > NOW() ORDER BY created_at DESC";
$stmt = $pdo->prepare($noticesQuery);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = [
            'type' => 'Notice',
            'message' => 'New notice: ' . $row['title'],
        ];
    }
}

// Close the database connection
$pdo = null;

// Output the notifications as a JSON response
echo json_encode($notifications);
?>

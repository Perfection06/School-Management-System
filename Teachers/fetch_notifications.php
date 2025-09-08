<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header("Location: login.php");
    exit;
}

include('database_connection.php');


// Query to fetch events with start_date in the future
$query = "SELECT title, start_date FROM events WHERE start_date > NOW() ORDER BY start_date ASC";

$result = $conn->query($query);

$notifications = [];

$eventsQuery = "SELECT title, start_date FROM events WHERE start_date > NOW() ORDER BY start_date ASC";
$eventsResult = $conn->query($eventsQuery);

if ($eventsResult->num_rows > 0) {
    while ($row = $eventsResult->fetch_assoc()) {
        $notifications[] = [
            'type' => 'Event',
            'message' => 'Upcoming event: ' . $row['title'] . ' starting on ' . date('F j, Y, g:i a', strtotime($row['start_date'])),
        ];
    }
}

$noticesQuery = "
    SELECT title FROM notices WHERE end_date > NOW() ORDER BY created_at DESC
";

// Fetch notices
$noticesResult = $conn->query($noticesQuery);

if ($noticesResult->num_rows > 0) {
    while ($row = $noticesResult->fetch_assoc()) {
        $notifications[] = [
            'type' => 'Notice',
            'message' => 'New notice: ' . $row['title'],
        ];
    }
}

$conn->close();


echo json_encode($notifications);
?>

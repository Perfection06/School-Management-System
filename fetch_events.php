<?php

include ("database_connection.php");

// Get year and month from the query parameters
$year = isset($_GET['year']) ? (int)$_GET['year'] : 2024;
$month = isset($_GET['month']) ? (int)$_GET['month'] : 12;

// Query to fetch events for the selected year and month
$sql = "SELECT title, description, start_date, end_date FROM events 
        WHERE YEAR(start_date) = $year AND MONTH(start_date) = $month
        ORDER BY start_date";

// Execute the query
$result = $conn->query($sql);

// Prepare an array for events
$events = [];
if ($result->num_rows > 0) {
    // Fetch all events
    while($row = $result->fetch_assoc()) {
        $events[] = [
            'title' => $row['title'],
            'description' => $row['description'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date']
        ];
    }
} else {
    // No events found
    $events = [];
}

// Return events as JSON
echo json_encode($events);

// Close the connection
$conn->close();
?>

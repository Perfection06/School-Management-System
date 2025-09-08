<?php
include('database_connection.php');


// Query to fetch events with start_date in the future
$query = "SELECT title, start_date FROM events WHERE start_date > NOW() ORDER BY start_date ASC";

$result = $conn->query($query);


//messages

// Prepare an array to store notifications
$notifications = [];

// 1. Query to fetch unread messages from user_messages table
$userMessagesQuery = "
    SELECT um.sender_username, um.content, u.role 
    FROM user_messages um
    INNER JOIN user u ON um.sender_username = u.username
    WHERE um.is_read = 0
    ORDER BY um.timestamp DESC
";

// Fetch messages for users (Teacher, Staff, etc.)
$userMessagesResult = $conn->query($userMessagesQuery);

if ($userMessagesResult->num_rows > 0) {
    while ($row = $userMessagesResult->fetch_assoc()) {
        $notifications[] = [
            'type' => 'Message',
            'message' => $row['sender_username'] . ' (' . $row['role'] . ') sent a new message: ' ,
        ];
    }
}

// 2. Query to fetch unread messages from student_messages table
$studentMessagesQuery = "
    SELECT sm.sender_username, sm.content, s.grade_id, g.grade_name
    FROM student_messages sm
    INNER JOIN Students s ON sm.sender_username = s.username
    INNER JOIN grades g ON s.grade_id = g.id
    WHERE sm.is_read = 0
    ORDER BY sm.timestamp DESC
";

// Fetch messages for students
$studentMessagesResult = $conn->query($studentMessagesQuery);

if ($studentMessagesResult->num_rows > 0) {
    while ($row = $studentMessagesResult->fetch_assoc()) {
        $notifications[] = [
            'type' => 'Message',
            'message' => $row['sender_username'] . ' (Grade: ' . $row['grade_name'] . ') sent a new message: ',
        ];
    }
}

// 3. Query to fetch events (same as before)
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

// Close database connection
$conn->close();

// Return notifications as a JSON response
echo json_encode($notifications);
?>

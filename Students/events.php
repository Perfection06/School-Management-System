<?php
session_start();

// Check if the user is logged in and is a Student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

include("database_connection.php");

// Get year and month from the query parameters, default to current month and year
$year = isset($_GET['year']) ? (int)$_GET['year'] : date("Y");
$month = isset($_GET['month']) ? (int)$_GET['month'] : date("m");

// Query to fetch events for the selected year and month
$sql = "SELECT title, description, start_date, end_date, created_at FROM events 
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
            'end_date' => $row['end_date'],
            'created_at' => $row['created_at']
        ];
    }
} else {
    // No events found
    $events = [];
}

// Fetch the calendar for the selected month
$firstDayOfMonth = strtotime("$year-$month-01");
$lastDayOfMonth = strtotime("$year-$month-" . date('t', $firstDayOfMonth));

$calendarDates = [];
for ($day = 1; $day <= date('t', $firstDayOfMonth); $day++) {
    $calendarDates[] = [
        'date' => date("Y-m-d", strtotime("$year-$month-$day")),
        'events' => []
    ];
}

// Check if any event falls on a particular date
foreach ($events as $event) {
    foreach ($calendarDates as $key => $date) {
        if ($event['start_date'] <= $date['date'] && $event['end_date'] >= $date['date']) {
            $calendarDates[$key]['events'][] = $event;
        }
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Events</title>
    <style>
        /* Basic styles for calendar */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        .nav-buttons {
            text-align: center;
            margin: 20px 0;
        }

        .nav-buttons a {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 0 5px;
            font-size: 16px;
        }

        .nav-buttons a:hover {
            background-color: #0056b3;
        }

        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            max-width: 80%;
            margin: 20px auto;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
        }

        .calendar .day {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px;
            background-color: #f9f9f9;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
            position: relative;
            cursor: pointer;
            transition: background-color 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .calendar .day:hover {
            background-color: #e0e0e0;
        }

        .calendar .highlighted {
            background-color: #ffeb3b;
            color: #000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .calendar .day span {
            font-size: 14px;
            color: #333;
        }

        /* Event details section */
        .event-details {
            background-color: #fff;
            padding: 30px;
            margin-top: 40px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 80%;
            margin: 0 auto;
        }

        .event-details h3 {
            margin-top: 0;
            font-size: 24px;
            color: #333;
        }

        .event-details p {
            font-size: 16px;
            color: #666;
        }

        .event-details hr {
            margin-top: 20px;
            border: 1px solid #ddd;
        }

        /* Additional Styling for Grid Days */
        .calendar .day {
            width: 45px; /* Smaller width for the day box */
            height: 45px; /* Smaller height for square look */
            font-size: 14px;
            border-radius: 5px;
        }

        .calendar .day .event {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background-color: #ff6347;
            color: white;
            padding: 3px 5px;
            font-size: 10px;
            border-radius: 4px;
            opacity: 0.8;
        }

    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<h2>Student Events for <?php echo date("F Y", strtotime("$year-$month-01")); ?></h2>

<!-- Navigation Buttons -->
<div class="nav-buttons">
    <a href="?year=<?php echo $year; ?>&month=<?php echo $month-1; ?>">Previous Month</a>
    <a href="?year=<?php echo $year; ?>&month=<?php echo $month+1; ?>">Next Month</a>
</div>

<!-- Calendar Section -->
<div class="calendar">
    <?php
    // Display the calendar header with days of the week
    $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    foreach ($daysOfWeek as $day) {
        echo "<div class='day'>$day</div>";
    }

    // Display the calendar days and events
    foreach ($calendarDates as $date) {
        $hasEvent = !empty($date['events']);
        echo "<div class='day" . ($hasEvent ? ' highlighted' : '') . "'>";
        echo date("d", strtotime($date['date']));
        echo "</div>";
    }
    ?>
</div>

<!-- Event Details Section -->
<div class="event-details">
    <h3>Event Details</h3>
    <?php if (count($events) > 0): ?>
        <ul>
            <?php foreach ($events as $event): ?>
                <li>
                    <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                    <p><?php echo htmlspecialchars($event['description']); ?></p>
                    <p><strong>Start Date:</strong> <?php echo htmlspecialchars($event['start_date']); ?></p>
                    <p><strong>End Date:</strong> <?php echo htmlspecialchars($event['end_date']); ?></p>
                    <hr>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No events scheduled for this month.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php

// Database connection
include("db_connection.php");

// Query to get event title and event start date from the events table
$eventsQuery = "
    SELECT title, start_date 
    FROM events
    ORDER BY start_date ASC
";
$stmt = $pdo->prepare($eventsQuery);
$stmt->execute();
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- event_widget.php -->
<div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 animate-fadeIn">
    <h3 class="text-lg font-semibold mb-4 text-center text-gray-800">Upcoming Events</h3>
    <div class="max-h-64 overflow-y-auto space-y-4">
        <?php foreach ($events as $event): ?>
            <div class="p-4 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors">
                <div class="font-bold text-blue-600"><?php echo htmlspecialchars($event['title']); ?></div>
                <div class="text-sm text-gray-600"><?php echo date("F j, Y", strtotime($event['start_date'])); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
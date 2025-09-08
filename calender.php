<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include ("db_connection.php");


$currentDate = date('Y-m-d H:i:s'); 


$stmt = $pdo->prepare("DELETE FROM events WHERE end_date < ?");
$stmt->execute([$currentDate]);


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Calendar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
            height: 100vh;
            color: #333;
        }
        #mainContent {
            flex: 3;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        #sidebar {
            flex: 1;
            border-left: 2px solid #ddd;
            background-color: #f8f9fa;
            padding: 20px;
            margin-top: -100px;
            margin-right: -50px;
            height: 140%;
            overflow-y: auto;
            box-shadow: -3px 0 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin: 20px 0;
            color: #555;
        }
        #calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin: 20px;
            width: 90%;
            max-width: 800px;
            background: #f1f1f1;
            border-radius: 10px;
            padding: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .day {
            border: 2px solid #e0e0e0;
            height: 100px;
            position: relative;
            text-align: center;
            padding-top: 10px;
            cursor: pointer;
            border-radius: 8px;
            transition: background-color 0.3s, transform 0.2s;
            font-size: 14px;
            font-weight: bold;
            background-color: #fff;
        }
        .day:hover {
            background-color: #d1eaff;
            transform: scale(1.05);
        }
        .selected {
            background-color: #81d4fa;
            border-color: #29b6f6;
            color: white;
        }

        .current-day {
            background-color: #ffeb3b; /* Yellow background for the current day */
            border-color: #ff9800; /* Orange border color */
            color: black; /* Make the text color black */
            font-weight: bold;
        }

        #addEventForm {
            display: flex;
            flex-direction: column;
            padding: 20px;
            border: 1px solid #ddd;
            background: white;
            width: 90%;
            max-width: 400px;
            box-shadow: 0px 3px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border-radius: 10px;
        }
        #addEventForm input, #addEventForm textarea, #addEventForm button {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            box-sizing: border-box;
            font-size: 14px;
        }
        #addEventForm button {
            background-color: #007bff;
            color: white;
            border: none;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        #addEventForm button:hover {
            background-color: #0056b3;
        }
        #eventList {
            list-style: none;
            padding: 0;
        }
        #eventList li {
            margin-bottom: 10px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #e9ecef;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        #eventList li:hover {
            transform: scale(1.02);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        #eventList li strong {
            display: block;
            font-size: 16px;
            margin-bottom: 5px;
            color: #007bff;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <div id="mainContent">
        <h1>Dynamic Calendar</h1>
        <div id="calendar"></div>

        <div id="addEventForm">
            <input type="text" id="eventTitle" placeholder="Event Title" required>
            <textarea id="eventDescription" placeholder="Event Description" rows="4" required></textarea>
            <button id="saveEventButton">Save Event</button>
        </div>
    </div>

    <div id="sidebar">
        <h2>Event List</h2>
        <ul id="eventList"></ul>
    </div>

    <script>
        const calendar = document.getElementById('calendar');
        const addEventForm = document.getElementById('addEventForm');
        const saveEventButton = document.getElementById('saveEventButton');
        const eventTitleInput = document.getElementById('eventTitle');
        const eventDescriptionInput = document.getElementById('eventDescription');
        const eventList = document.getElementById('eventList');

        const daysInMonth = 31; // December 2024
        let events = []; // To store events locally

        let selectedStartDate = null;
        let selectedEndDate = null;

        // Fetch events from the database
        function fetchEvents() {
            fetch(`fetch_events.php?year=2024&month=12`)
                .then(response => response.json())
                .then(data => {
                    if (Array.isArray(data)) {
                        events = data; // Update the events array
                    } else {
                        events = []; // Clear events if response is invalid
                    }
                    renderCalendar(); // Re-render the calendar with fetched events
                    renderEventList(); // Re-render the event list
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    events = []; // Clear events on error
                    renderCalendar();
                    renderEventList();
                });
        }

        // Generate the calendar
        // Generate the calendar
function renderCalendar() {
    calendar.innerHTML = ''; // Clear existing calendar content

    const currentDate = new Date();
    const currentDay = currentDate.getDate();  // Get the current day (1-31)
    const currentMonth = currentDate.getMonth() + 1;  // Get the current month (1-12)
    const currentYear = currentDate.getFullYear(); // Get the current year (yyyy)

    for (let day = 1; day <= daysInMonth; day++) {
        const dayDiv = document.createElement('div');
        dayDiv.classList.add('day');
        dayDiv.dataset.day = day;

        // Add day number
        dayDiv.textContent = day;

        // Check if this day is the current day
        if (day === currentDay && currentMonth === 12 && currentYear === 2024) {
            dayDiv.classList.add('current-day');  // Add a special class to highlight today's date
        }

        dayDiv.addEventListener('click', () => {
            if (!selectedStartDate || selectedEndDate) {
                selectedStartDate = day;
                selectedEndDate = null;
                clearSelections();
                dayDiv.classList.add('selected');
            } else if (selectedStartDate && !selectedEndDate && day > selectedStartDate) {
                selectedEndDate = day;
                for (let d = selectedStartDate; d <= selectedEndDate; d++) {
                    document.querySelector(`[data-day="${d}"]`).classList.add('selected');
                }
            } else {
                selectedStartDate = day;
                selectedEndDate = null;
                clearSelections();
                dayDiv.classList.add('selected');
            }
        });

        calendar.appendChild(dayDiv);
    }
}


        function clearSelections() {
            document.querySelectorAll('.day').forEach(day => day.classList.remove('selected'));
        }

        // Render the event list
        function renderEventList() {
            eventList.innerHTML = ''; // Clear existing event list

            events.forEach(event => {
                const listItem = document.createElement('li');
                listItem.innerHTML = `<strong>${event.title}</strong><br>${event.description}<br>${event.start_date} - ${event.end_date}`;
                eventList.appendChild(listItem);
            });
        }

        // Save event
        saveEventButton.addEventListener('click', () => {
            const title = eventTitleInput.value.trim();
            const description = eventDescriptionInput.value.trim();

            if (!selectedStartDate || !title || !description) {
                alert('Please fill in all fields and select a date range.');
                return;
            }

            const start_date = `2024-12-${String(selectedStartDate).padStart(2, '0')}`;
            const end_date = selectedEndDate ? `2024-12-${String(selectedEndDate).padStart(2, '0')}` : start_date;

            const newEvent = {
                title,
                description,
                start_date,
                end_date
            };

            // Save event to the database
            fetch('save_event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(newEvent),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        events.push(newEvent); // Add the new event to the local events array
                        renderCalendar(); // Re-render the calendar
                        renderEventList(); // Update the event list
                        alert('Event saved successfully!');
                    } else {
                        alert('Error saving event: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error saving event: ' + error);
                });

            eventTitleInput.value = '';
            eventDescriptionInput.value = '';
            clearSelections();
        });

        // Initial fetch and render
        fetchEvents(); // Load events from the database

    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Notifications</title>
    <style>
        /* Notification Icon Style */
        .notification-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            cursor: pointer;
            font-size: 24px;
            background-color: #3498db;
            color: white;
            padding: 12px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;
        }
        .notification-icon:hover {
            background-color: #2980b9;
        }
        .notification-icon .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: bold;
        }

        /* Dropdown Notifications Style */
        .dropdown {
            display: none;
            position: absolute;
            bottom: 70px;
            right: 20px;
            background: white;
            border: 1px solid #ccc;
            width: 320px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            z-index: 1000;
            overflow: hidden;
        }
        .dropdown.show {
            display: block;
        }
        .dropdown .notification-item {
            padding: 15px;
            background-color: #f9f9f9;
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s;
        }
        .dropdown .notification-item:hover {
            background-color: #f1f1f1;
        }
        .dropdown .notification-item:last-child {
            border-bottom: none;
        }
        .dropdown .notification-item strong {
            color: #2c3e50;
            font-weight: 600;
        }
        .dropdown .notification-item .message {
            display: block;
            color: #7f8c8d;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .dropdown p {
            margin: 0;
            padding: 10px;
            color: #7f8c8d;
            text-align: center;
        }
    </style>
</head>
<body>

<!-- Notification Icon -->
<div class="notification-icon" onclick="toggleDropdown()">
    🔔 <span class="badge" id="notificationCount">0</span>
</div>

<!-- Notification Dropdown -->
<div class="dropdown" id="notificationDropdown">
    <div id="notificationsList">
        <p>No new notifications</p>
    </div>
</div>

<script>
    // Toggle dropdown visibility
    function toggleDropdown() {
        document.getElementById('notificationDropdown').classList.toggle('show');
    }

    // Fetch notifications from the server
    function fetchNotifications() {
        fetch('./fetch_notifications.php')
            .then(response => response.json())
            .then(data => {
                const notificationList = document.getElementById('notificationsList');
                const notificationCount = document.getElementById('notificationCount');
                
                notificationList.innerHTML = '';
                if (data.length > 0) {
                    notificationCount.textContent = data.length;
                    data.forEach(notification => {
                        const item = document.createElement('div');
                        item.classList.add('notification-item');
                        item.innerHTML = `
                            <strong>${notification.type}</strong>
                            <span class="message">${notification.message}</span>
                        `;
                        notificationList.appendChild(item);
                    });
                } else {
                    notificationCount.textContent = '0';
                    notificationList.innerHTML = '<p>No new notifications</p>';
                }
            });
    }

    // Refresh notifications every time the page loads
    window.onload = fetchNotifications;
</script>

</body>
</html>

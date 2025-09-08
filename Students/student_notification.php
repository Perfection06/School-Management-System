<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'sms';
$dbUsername = 'root';
$dbPassword = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbUsername, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

$studentId = $_SESSION['id'];

// Query to fetch the student's grade
$sql = "SELECT grade FROM Students WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student not found");
}

$studentGrade = $student['grade']; // This is the grade_name in the students table

$notifications = [];

// Event notifications (same as before)
$currentDate = date("Y-m-d");
$threeDaysFromNow = date("Y-m-d", strtotime("+3 days"));

// Query to fetch upcoming events
$sql = "SELECT e.title, ed.event_date
        FROM events e
        JOIN event_dates ed ON e.event_id = ed.event_id
        WHERE ed.event_date BETWEEN ? AND ?
        ORDER BY ed.event_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$currentDate, $threeDaysFromNow]);

// Process and format event notifications
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = [
            "type" => "Upcoming Event",
            "message" => "Title: {$row['title']}<br>Date: " . date("M d", strtotime($row["event_date"]))
        ];
    }
}

// Student-specific notifications (assignments and study materials)
$sql = "SELECT a.title, a.end_date FROM assignments a
        JOIN grades g ON a.class_id = g.id
        WHERE g.grade_name = ? AND a.end_date >= CURDATE()
        ORDER BY a.end_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$studentGrade]); // Using the grade_name directly in the query

// Process and format student-specific notifications
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = [
            "type" => "New Assignment/Study Material",
            "message" => "Title: {$row['title']}<br>Available until: " . date("M d, Y", strtotime($row["end_date"])) . "<br><i>Click to check available files.</i>"
        ];
    }
}

// Fetch active admin notices
$sql = "SELECT title, content, end_date FROM notices WHERE end_date >= ? ORDER BY end_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$currentDate]);

// Process and format admin-created notice notifications
if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $notifications[] = [
            "type" => "New Notice",
            "message" => "Check in Notice Page!<br>Title: {$row['title']}<br>End Date: " . date("M d, Y", strtotime($row["end_date"]))
        ];
    }
}

// Output notifications as JSON
header('Content-Type: application/json');
echo json_encode($notifications);
?>

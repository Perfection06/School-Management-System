<?php
// Assuming you're using PDO for database connection
$dsn = "mysql:host=localhost;dbname=Reliance";
$username = "root";
$password = "";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);
    $title = $data['title'];
    $description = $data['description'];
    $start_date = $data['start_date'];
    $end_date = $data['end_date'];

    // Insert event into the database
    $stmt = $pdo->prepare("INSERT INTO events (title, description, start_date, end_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $start_date, $end_date]);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>

<?php
// Database connection
require_once 'database_connection.php';

// Get subject_id from the GET request
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;

if ($subject_id > 0) {
    // Fetch grades assigned to the subject
    $query = "
        SELECT gs.grade_id, g.grade_name
        FROM grade_subject gs
        LEFT JOIN grades g ON gs.grade_id = g.id
        WHERE gs.subject_id = ?
    ";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $subject_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $grades = $result->fetch_all(MYSQLI_ASSOC);

        // Return grades as JSON
        header('Content-Type: application/json');
        echo json_encode($grades);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database query error.']);
    }
} else {
    // Invalid or missing subject ID
    http_response_code(400);
    echo json_encode(['error' => 'Invalid subject ID']);
}

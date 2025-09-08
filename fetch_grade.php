<?php
require_once 'database_connection.php';

// Fetch the subject ID
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;

// Fetch grades associated with the subject
$sql = "
    SELECT g.id AS grade_id, g.grade_name
    FROM subject_grades sg
    JOIN grades g ON sg.grade_id = g.id
    WHERE sg.subject_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$result = $stmt->get_result();

$grades = [];
while ($row = $result->fetch_assoc()) {
    $grades[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($grades);
?>

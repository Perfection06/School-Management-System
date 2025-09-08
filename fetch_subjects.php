<?php
require_once 'db_connection.php';

if (isset($_GET['grade_id'])) {
    $gradeId = intval($_GET['grade_id']);
    
    // Fetch subjects for the selected grade
    $stmt = $pdo->prepare("
        SELECT s.id, s.subject_name
        FROM subjects s
        JOIN grade_subject gs ON s.id = gs.subject_id
        WHERE gs.grade_id = ?
    ");
    $stmt->execute([$gradeId]);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($subjects);
}

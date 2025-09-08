<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'NoClass_Teacher') {
    header("Location: login.php");
    exit;
}

include("db_connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['completion_status'] as $chapter_id => $status) {
            // Check required data for fields that are not disabled
            $completion_status = isset($_POST['completion_status'][$chapter_id]) ? 1 : 0;
            $finished_on_time = $_POST['finished_on_time'][$chapter_id];

            // Handle optional fields when finished_on_time is "No"
            $extra_periods = ($finished_on_time == "0" && isset($_POST['extra_periods'][$chapter_id])) 
                ? $_POST['extra_periods'][$chapter_id] 
                : null;
            $reason = ($finished_on_time == "0" && isset($_POST['reason'][$chapter_id])) 
                ? $_POST['reason'][$chapter_id] 
                : null;

            // Prepare the query
            $queryUpdate = "
                UPDATE chapters
                SET completion_status = :completion_status,
                    finished_on_time = :finished_on_time,
                    extra_periods = :extra_periods,
                    reason = :reason
                WHERE id = :chapter_id
            ";
            $stmtUpdate = $pdo->prepare($queryUpdate);
            $stmtUpdate->execute([
                ':completion_status' => $completion_status,
                ':finished_on_time' => $finished_on_time,
                ':extra_periods' => $extra_periods,
                ':reason' => $reason,
                ':chapter_id' => $chapter_id
            ]);
        }
        echo "<script>alert('Chapter details updated successfully!'); window.location.href = 'Sub_Teacher_Dashboard.php';</script>";
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Error updating chapter details: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid request method.'); window.location.href = 'teacher_dashboard.php';</script>";
    exit;
}
?>
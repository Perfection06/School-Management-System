<?php
include('database_connection.php');

// Check if 'type' is passed via GET
if (!isset($_GET['type'])) {
    echo json_encode(['error' => 'Recipient type not specified']);
    exit();
}

$type = $_GET['type'];  // Recipient type (students, teachers, noClassTeachers, staff)
$grade_id = isset($_GET['grade_id']) ? (int)$_GET['grade_id'] : null;  // Grade ID for students, cast to integer for safety

$users = [];

// Debug: Log the received parameters
error_log("Received type: " . $type);
error_log("Received grade_id: " . $grade_id);

switch ($type) {
    case 'students':
        if (!$grade_id) {
            echo json_encode(['error' => 'Grade ID is required for students']);
            exit();
        }

        // Fetch students based on the selected grade
        $query = "SELECT students.username, students.name 
                  FROM students
                  WHERE students.grade_id = ?";
        
        // Prepare statement and bind parameters to prevent SQL injection
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $grade_id);
        break;

    case 'teachers':
        // Fetch teachers
        $query = "SELECT user.username, teacher.full_name AS name 
                  FROM teacher 
                  JOIN user ON teacher.username = user.username
                  WHERE user.active = 1";
        
        // Prepare statement
        $stmt = $conn->prepare($query);
        break;

    case 'noClassTeachers':
        // Fetch no-class teachers
        $query = "SELECT user.username, noclass_teacher.full_name AS name 
                  FROM noclass_teacher 
                  JOIN user ON noclass_teacher.username = user.username
                  WHERE user.active = 1";
        
        // Prepare statement
        $stmt = $conn->prepare($query);
        break;

    case 'staff':
        // Fetch staff members
        $query = "SELECT user.username, staff.full_name AS name 
                  FROM staff 
                  JOIN user ON staff.username = user.username
                  WHERE user.active = 1";
        
        // Prepare statement
        $stmt = $conn->prepare($query);
        break;

    default:
        echo json_encode(['error' => 'Invalid recipient type']);
        exit();
}

// Execute the prepared query
if (isset($stmt)) {
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check for query errors
    if (!$result) {
        error_log("MySQL error: " . $stmt->error);  // Log the error
        echo json_encode(['error' => 'Error in query: ' . $stmt->error]);
        exit();
    }

    // Fetch and return users
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    // Debug: Log the fetched users
    error_log("Fetched users: " . print_r($users, true));

    // Return the users as a JSON response
    echo json_encode($users);
} else {
    echo json_encode(['error' => 'Query preparation failed']);
}
?>

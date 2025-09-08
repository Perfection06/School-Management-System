<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'NoClass_Teacher') {
    header("Location: login.php");
    exit;
}
// Database connection
include("db_connection.php");

// Fetch the teacher's username from the session
$username = $_SESSION['user']['username'];

// Function to check and delete expired assignments
function deleteExpiredAssignments($pdo, $username) {
    $currentDate = date('Y-m-d');

    // Fetch assignments for the logged-in teacher
    $stmt = $pdo->prepare("SELECT * FROM assignments WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($assignments as $assignment) {
        if ($assignment['end_date'] < $currentDate) {
            // Attempt to delete the assignment file
            if (file_exists($assignment['file_path'])) {
                if (!unlink($assignment['file_path'])) {
                    echo "Error: Unable to delete file '{$assignment['file_path']}'. Check file permissions.";
                }
            }

            // Delete the assignment record from the database
            $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = :id");
            $stmt->execute(['id' => $assignment['id']]);

            echo "Assignment titled '{$assignment['title']}' has been deleted due to the passed end date.<br>";
        }
    }
}

// Call the function to delete expired assignments
deleteExpiredAssignments($pdo, $username);

// Fetch the teacher's teaching classes from the database
$stmt = $pdo->prepare("SELECT teaching_classes FROM noclass_teacher WHERE username = :username");
$stmt->execute(['username' => $username]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all assignments for the logged-in staff, including grade names
$stmt = $pdo->prepare("
SELECT a.*, g.grade_name 
FROM assignments a
JOIN grades g ON a.class_id = g.id
WHERE a.username = :username
");
$stmt->execute(['username' => $username]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Decode the JSON field teaching_classes into an array of class IDs
$teaching_classes = json_decode($teacher['teaching_classes'], true);

// Fetch all available grades to display in the dropdown (based on the teaching classes of the teacher)
$stmt = $pdo->prepare("SELECT * FROM grades WHERE id IN (" . implode(',', array_map('intval', $teaching_classes)) . ")");
$stmt->execute();
$grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $title = $_POST['title'];
    $end_date = $_POST['end_date'];
    $selected_class_id = $_POST['class_id'];  // Get the selected class ID

    // File validation
    $allowedExtensions = ['rar', 'doc', 'docx', 'pdf'];
    $maxFileSize = 100 * 1024 * 1024; // 100 MB

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions) && $fileSize <= $maxFileSize) {
            $uploadDir = '../uploads/assignments/';
            $newFileName = uniqid() . '_' . $fileName;
            $filePath = $uploadDir . '/' . $newFileName;

            if (move_uploaded_file($fileTmpPath, $filePath)) {
                // Insert the assignment into the database for the selected class
                $stmt = $pdo->prepare("
                    INSERT INTO assignments (title, file_path, end_date, username, class_id)
                    VALUES (:title, :file_path, :end_date, :username, :class_id)
                ");
                $stmt->execute([
                    'title' => $title,
                    'file_path' => $filePath,
                    'end_date' => $end_date,
                    'username' => $username, // Use the logged-in user's username
                    'class_id' => $selected_class_id // Store assignment for the selected class
                ]);

                echo "<script>alert('File uploaded successfully!'); window.location.href='assignment.php';</script>";
            } else {
                echo "<script>alert('Error uploading the file.'); window.location.href='assignment.php';</script>";
            }
        } else {
            echo "Invalid file type or size exceeded (100MB).";
        }
    } else {
        echo "Error: No file uploaded or an error occurred.";
    }
}

// Fetch all assignments for the logged-in teacher
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE username = :username");
$stmt->execute(['username' => $username]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Assignment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2, h3 {
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"], input[type="file"], input[type="date"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        .file-container {
            max-width: 800px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .delete-btn {
            color: #dc3545;
            text-decoration: none;
        }

        .delete-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
<!-- File Upload Form -->
<div class="container">
    <h2>Upload Assignment or Study Material</h2>
    <form action="assignment.php" method="POST" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required>
        
        <label for="file">Select File (RAR, DOC, PDF up to 100MB):</label>
        <input type="file" id="file" name="file" accept=".rar,.doc,.docx,.pdf" required>
        
        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required>

        <!-- Dropdown for the class to send assignment to -->
        <label for="class_id">Select Class:</label>
        <select name="class_id" id="class_id" required>
            <?php foreach ($grades as $grade): ?>
                <option value="<?php echo $grade['id']; ?>"><?php echo htmlspecialchars($grade['grade_name']); ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="upload">Upload</button>
    </form>
</div>

<!-- Uploaded Files Section -->
<div class="file-container">
    <h3>Uploaded Assignments</h3>
    <?php 
    // Fetch all assignments for the logged-in staff, including grade names
    $stmt = $pdo->prepare("
        SELECT a.*, g.grade_name 
        FROM assignments a
        LEFT JOIN grades g ON a.class_id = g.id
        WHERE a.username = :username
    ");
    $stmt->execute(['username' => $username]);
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if (count($assignments) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Grade</th>
                    <th>File</th>
                    <th>End Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assignment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                        <td><?php echo isset($assignment['grade_name']) ? htmlspecialchars($assignment['grade_name']) : 'N/A'; ?></td>
                        <td><a href="<?php echo $assignment['file_path']; ?>" target="_blank">Download</a></td>
                        <td><?php echo htmlspecialchars($assignment['end_date']); ?></td>
                        <td>
                            <a href="?delete_id=<?php echo $assignment['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this file?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No assignments uploaded yet.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Staff') {
    header("Location: login.php");
    exit;
}



include ("db_connection.php");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Only handle AJAX requests with a specified action
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'get_subjects') {
        $gradeId = $_GET['grade'] ?? null;

        // Ensure $gradeId is set
        if (!$gradeId) {
            echo json_encode(['error' => 'Grade ID is missing']);
            exit();
        }

        // Fetch all subjects and check if each is assigned to the grade
        try {
            $stmt = $pdo->prepare("
                SELECT s.id, s.subject_name, (gs.grade_id IS NOT NULL) AS isAssigned
                FROM subjects s
                LEFT JOIN grade_subject gs ON s.id = gs.subject_id AND gs.grade_id = ?
            ");
            $stmt->execute([$gradeId]);
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($subjects); // Return the subjects in JSON format
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

    if ($_GET['action'] === 'update_subject') {
        $data = json_decode(file_get_contents('php://input'), true);
        $gradeId = $data['gradeId'] ?? null;
        $subjectId = $data['subjectId'] ?? null;
        $isChecked = $data['isChecked'] ?? null;

        // Ensure required data is present
        if (!$gradeId || !$subjectId || !isset($isChecked)) {
            echo json_encode(['error' => 'Incomplete data']);
            exit();
        }

        try {
            if ($isChecked) {
                // Insert association if checked
                $stmt = $pdo->prepare("INSERT IGNORE INTO grade_subject (grade_id, subject_id) VALUES (?, ?)");
                $stmt->execute([$gradeId, $subjectId]);
            } else {
                // Delete association if unchecked
                $stmt = $pdo->prepare("DELETE FROM grade_subject WHERE grade_id = ? AND subject_id = ?");
                $stmt->execute([$gradeId, $subjectId]);
            }
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit();
    }

    // Return an error message if no action is matched
    echo json_encode(['error' => 'Invalid action']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Subjects to Grade</title>
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            color: #333;
        }

        .ass_container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
        }

        h1, h2 {
            color: #444;
            text-align: center;
        }

        h1 {
            margin-bottom: 30px;
        }

        .section {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        /* Form Section */
        .assign_container {
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 14px;
        }

        select, .subjects-container {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        select {
            font-size: 16px;
        }

        .subjects-container {
            max-height: 200px;
            overflow-y: auto;
        }

        .subjects-container label {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .subjects-container input[type="checkbox"] {
            margin-right: 10px;
        }

        /* View Section */
        .view-assigned-container {
            margin-top: 30px;
        }

        .view-assigned-container h3 {
            margin-top: 15px;
            font-size: 18px;
            color: #555;
        }

        .view-assigned-container ul {
            margin: 10px 0 0;
            padding-left: 20px;
            list-style-type: disc;
        }

        .view-assigned-container li {
            font-size: 16px;
            margin-bottom: 5px;
        }

        .no-data {
            font-style: italic;
            color: #777;
        }

        /* Responsiveness */
        @media (max-width: 768px) {
            .section {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <div class="ass_container">
        <!-- Page Title -->
        <h1>Assign Subjects to Grade</h1>

        <!-- Form Section -->
        <div class="section assign_container">
            <h2>Assign Subjects</h2>
            <form id="subjectGradeForm">
                <label for="grade">Select Grade:</label>
                <select name="grade" id="grade" onchange="fetchSubjectsForGrade()">
                    <option value="" disabled selected>Select a grade</option>
                    <?php
                    try {
                        $grades = $pdo->query("SELECT id, grade_name FROM grades")->fetchAll(PDO::FETCH_ASSOC);
                        if (!$grades) {
                            echo "<option disabled>No grades available</option>";
                        } else {
                            foreach ($grades as $grade) {
                                echo "<option value='{$grade['id']}'>" . htmlspecialchars($grade['grade_name']) . "</option>";
                            }
                        }
                    } catch (PDOException $e) {
                        echo "<option disabled>Error fetching grades</option>";
                    }
                    ?>
                </select>

                <div id="subjectsContainer" class="subjects-container">
                    <p class="no-data">Select a grade to view and assign subjects.</p>
                </div>
            </form>
        </div>
        <a href="./assign_subjects.php">Refresh</a>
        <!-- View Section -->
        <div class="section view-assigned-container">
            <h2>View Assigned Subjects</h2>
            <?php
            try {
                $grades = $pdo->query("SELECT id, grade_name FROM grades")->fetchAll(PDO::FETCH_ASSOC);

                if ($grades) {
                    foreach ($grades as $grade) {
                        echo "<h3>Grade: " . htmlspecialchars($grade['grade_name']) . "</h3>";

                        // Fetch assigned subjects for the current grade
                        $stmt = $pdo->prepare("
                            SELECT s.subject_name 
                            FROM subjects s
                            INNER JOIN grade_subject gs ON s.id = gs.subject_id
                            WHERE gs.grade_id = ?
                        ");
                        $stmt->execute([$grade['id']]);
                        $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if ($subjects) {
                            echo "<ul>";
                            foreach ($subjects as $subject) {
                                echo "<li>" . htmlspecialchars($subject['subject_name']) . "</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p class='no-data'>No subjects assigned to this grade.</p>";
                        }
                    }
                } else {
                    echo "<p class='no-data'>No grades found in the database.</p>";
                }
            } catch (PDOException $e) {
                echo "<p class='no-data'>Error fetching assigned subjects.</p>";
            }
            ?>
        </div>
    </div>



<script>
async function fetchSubjectsForGrade() {
    const gradeId = document.getElementById("grade").value;
    if (!gradeId) return;

    const response = await fetch(`assign_subjects.php?action=get_subjects&grade=${gradeId}`);
    
    if (!response.ok) {
        console.error("Failed to fetch subjects:", response);
        return;
    }

    const subjects = await response.json();
    const container = document.getElementById("subjectsContainer");
    container.innerHTML = "";

    subjects.forEach(subject => {
        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.value = subject.id;
        checkbox.checked = subject.isAssigned;
        checkbox.onchange = () => toggleSubject(gradeId, subject.id, checkbox.checked);

        const label = document.createElement("label");
        label.appendChild(checkbox);
        label.append(subject.subject_name);

        container.appendChild(label);
    });
}

async function toggleSubject(gradeId, subjectId, isChecked) {
    const response = await fetch(`assign_subjects.php?action=update_subject`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ gradeId, subjectId, isChecked })
    });

    if (!response.ok) {
        console.error("Failed to update subject:", response);
    }
}
</script>
</body>
</html>
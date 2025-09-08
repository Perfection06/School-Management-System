<?php 
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

include ("db_connection.php");

// Query to fetch all subjects (distinct) 
$querySubjects = "SELECT * FROM subjects ORDER BY subject_name";
$stmtSubjects = $pdo->query($querySubjects);
$subjects = $stmtSubjects->fetchAll(PDO::FETCH_ASSOC);

// Fetch grades for a specific subject (AJAX call will trigger this)
if (isset($_GET['subject_id'])) {
    $subject_id = $_GET['subject_id'];

    // Fetch grades for the selected subject
    $queryGrades = "
        SELECT g.grade_name, g.id AS grade_id
        FROM grade_subject gs
        JOIN grades g ON gs.grade_id = g.id
        WHERE gs.subject_id = :subject_id
    ";
    
    $stmtGrades = $pdo->prepare($queryGrades);
    $stmtGrades->execute([':subject_id' => $subject_id]);
    $grades = $stmtGrades->fetchAll(PDO::FETCH_ASSOC);

    // Return the grades as JSON
    echo json_encode($grades);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = $_POST['subject_id'];
    $grade_id = $_POST['grade_id'];

    // Loop through the chapters and insert them into the database
    $chapters = $_POST['chapters'];
    foreach ($chapters as $chapter) {
        $chapter_name = $chapter['chapter_name'];
        $periods_allocated = $chapter['periods_allocated'];
        $term = $chapter['term'];

        // Insert chapter data into the chapters table
        $insertQuery = "
            INSERT INTO chapters (chapter_name, periods_allocated, term, subject_id, grade_id)
            VALUES (:chapter_name, :periods_allocated, :term, :subject_id, :grade_id)
        ";

        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute([
            ':chapter_name' => $chapter_name,
            ':periods_allocated' => $periods_allocated,
            ':term' => $term,
            ':subject_id' => $subject_id,
            ':grade_id' => $grade_id,
        ]);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Chapter</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Center the form on the page */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }

        form {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 700px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        select, input {
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        select {
            width: 100%;
        }

        #chapters-container {
            margin-bottom: 20px;
        }

        .chapter-entry {
            margin-bottom: 20px;
        }

        .chapter-entry h3 {
            margin-bottom: 10px;
        }

        /* Flexbox for chapter inputs */
        .chapter-inputs {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .chapter-inputs label {
            flex: 0 0 150px; /* Fixed width for labels */
        }

        .chapter-inputs input {
            flex: 1;
        }

        #add-chapter {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #5a67d8;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #add-chapter:hover {
            background-color: #434190;
        }

        button[type="submit"] {
            width: 100%;
            padding: 10px 20px;
            background-color: #48bb78;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #2f855a;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>
    <form action="chapters.php" method="POST">
        <h1>Create Chapters</h1>

        <!-- Subject Selection -->
        <label for="subject">Select Subject:</label>
        <select name="subject_id" id="subject" onchange="fetchGrades()">
            <option value="">--Select Subject--</option>
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['id'] ?>"><?= $subject['subject_name'] ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Grade Selection -->
        <label for="grade">Select Grade:</label>
        <select name="grade_id" id="grade">
            <option value="">--Select Grade--</option>
        </select>

        <!-- Chapter Entries -->
        <div id="chapters-container">
            <div class="chapter-entry">
                <h3>Chapter 1</h3>
                <div class="chapter-inputs">
                    <label for="chapter_name">Chapter Name:</label>
                    <input type="text" name="chapters[0][chapter_name]" required>
                </div>
                <div class="chapter-inputs">
                    <label for="periods_allocated">No. of Periods Allocated:</label>
                    <input type="number" name="chapters[0][periods_allocated]" required>
                </div>
                <div class="chapter-inputs">
                    <label for="term">Term:</label>
                    <input type="text" name="chapters[0][term]" required>
                </div>
            </div>
        </div>

        <button type="button" id="add-chapter">Add Another Chapter</button>
        <button type="submit">Create Chapters</button>
    </form>

    <script>
        let chapterCount = 1;

        // Add more chapters dynamically
        $("#add-chapter").click(function() {
            chapterCount++;
            const chapterHtml = `
                <div class="chapter-entry">
                    <h3>Chapter ${chapterCount}</h3>
                    <div class="chapter-inputs">
                        <label for="chapter_name">Chapter Name:</label>
                        <input type="text" name="chapters[${chapterCount - 1}][chapter_name]" required>
                    </div>
                    <div class="chapter-inputs">
                        <label for="periods_allocated">No. of Periods Allocated:</label>
                        <input type="number" name="chapters[${chapterCount - 1}][periods_allocated]" required>
                    </div>
                    <div class="chapter-inputs">
                        <label for="term">Term:</label>
                        <input type="text" name="chapters[${chapterCount - 1}][term]" required>
                    </div>
                </div>
            `;
            $("#chapters-container").append(chapterHtml);
        });

        function fetchGrades() {
            var subjectId = $("#subject").val();
            if (subjectId) {
                $.ajax({
                    url: 'chapters.php',
                    method: 'GET',
                    data: { subject_id: subjectId },
                    dataType: 'json',
                    success: function(data) {
                        $("#grade").html('<option value="">--Select Grade--</option>'); // Reset grade dropdown
                        if (data.length > 0) {
                            data.forEach(function(grade) {
                                $("#grade").append('<option value="' + grade.grade_id + '">' + grade.grade_name + '</option>');
                            });
                        } else {
                            $("#grade").html('<option value="">No grades available</option>');
                        }
                    }
                });
            } else {
                $("#grade").html('<option value="">--Select Grade--</option>');
            }
        }
    </script>
</body>
</html>

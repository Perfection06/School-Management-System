<?php
include("db_connection.php");

// Query to get average marks by subject
$averageMarksBySubjectQuery = "
    SELECT sub.subject_name, AVG(sm.marks) AS average_marks
    FROM student_marks sm
    JOIN subjects sub ON sm.subject_id = sub.id
    GROUP BY sm.subject_id
    ORDER BY average_marks DESC
";
$stmt = $pdo->prepare($averageMarksBySubjectQuery);
$stmt->execute();
$averageMarksBySubject = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query to get average marks by grade
$averageMarksByGradeQuery = "
    SELECT g.grade_name, AVG(sm.marks) AS average_marks
    FROM student_marks sm
    JOIN students s ON sm.student_username = s.username
    JOIN grades g ON s.grade_id = g.id
    GROUP BY g.id
    ORDER BY average_marks DESC
";
$stmt = $pdo->prepare($averageMarksByGradeQuery);
$stmt->execute();
$averageMarksByGrade = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- results_widget.php -->
<div class="col-span-1 md:col-span-2 bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 animate-fadeIn">
    <h3 class="text-lg font-semibold mb-4 text-center text-gray-800">Exam Performance</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Average Marks by Subject -->
        <div class="bg-gray-50 p-4 rounded-md">
            <h4 class="text-md font-medium mb-2 text-center">Average Marks by Subject</h4>
            <canvas id="averageMarksBySubjectChart" class="w-full h-64"></canvas>
        </div>
        <!-- Average Marks by Grade -->
        <div class="bg-gray-50 p-4 rounded-md">
            <h4 class="text-md font-medium mb-2 text-center">Average Marks by Grade</h4>
            <canvas id="averageMarksByGradeChart" class="w-full h-64"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Average Marks by Subject Data
    var subjects = <?= json_encode(array_column($averageMarksBySubject, 'subject_name')) ?>;
    var subjectAvgMarks = <?= json_encode(array_column($averageMarksBySubject, 'average_marks')) ?>;

    var subjectChartCtx = document.getElementById('averageMarksBySubjectChart').getContext('2d');
    new Chart(subjectChartCtx, {
        type: 'bar',
        data: {
            labels: subjects,
            datasets: [{
                label: 'Average Marks',
                data: subjectAvgMarks,
                backgroundColor: '#4CAF50',
                borderColor: '#2c6e2d',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutBounce'
            }
        }
    });

    // Average Marks by Grade Data
    var grades = <?= json_encode(array_column($averageMarksByGrade, 'grade_name')) ?>;
    var gradeAvgMarks = <?= json_encode(array_column($averageMarksByGrade, 'average_marks')) ?>;

    var gradeChartCtx = document.getElementById('averageMarksByGradeChart').getContext('2d');
    new Chart(gradeChartCtx, {
        type: 'bar',
        data: {
            labels: grades,
            datasets: [{
                label: 'Average Marks',
                data: gradeAvgMarks,
                backgroundColor: '#FF5733',
                borderColor: '#c4411f',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutBounce'
            }
        }
    });
</script>
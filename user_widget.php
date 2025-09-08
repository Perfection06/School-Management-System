<?php
include 'database_connection.php';

// Initialize variables for counts
$totalTeachers = 0;
$totalNoClassTeachers = 0;
$totalStaff = 0;

// Fetch the counts for each role
// 1. Count Teachers
$teacherQuery = "SELECT COUNT(*) AS count FROM user WHERE role = 'Teacher'";
$result = $conn->query($teacherQuery);
if ($result && $row = $result->fetch_assoc()) {
    $totalTeachers = $row['count'];
}

// 2. Count No-Class Teachers
$noClassTeacherQuery = "SELECT COUNT(*) AS count FROM user WHERE role = 'NoClass_Teacher'";
$result = $conn->query($noClassTeacherQuery);
if ($result && $row = $result->fetch_assoc()) {
    $totalNoClassTeachers = $row['count'];
}

// 3. Count Staffs
$staffQuery = "SELECT COUNT(*) AS count FROM user WHERE role = 'Staff'";
$result = $conn->query($staffQuery);
if ($result && $row = $result->fetch_assoc()) {
    $totalStaff = $row['count'];
}

$conn->close();
?>

<!-- user_widget.php -->
<div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 animate-fadeIn">
    <h3 class="text-lg font-semibold mb-4 text-center text-gray-800">User Distribution</h3>
    <canvas id="userChart" class="w-full h-64"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('userChart').getContext('2d');
    const userChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Teachers', 'No-Class Teachers', 'Staff'],
            datasets: [{
                label: 'User Distribution',
                data: [<?= $totalTeachers ?>, <?= $totalNoClassTeachers ?>, <?= $totalStaff ?>],
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    enabled: true
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });
</script>
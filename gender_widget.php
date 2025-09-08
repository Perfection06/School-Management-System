<?php

include("db_connection.php");

// Query to get count of male students by joining Students and Student_Admissions tables
$maleQuery = "SELECT COUNT(*) AS male_count 
              FROM Students s 
              JOIN Student_Admissions sa ON s.id = sa.id 
              WHERE sa.student_gender = 'Male'";
$stmt = $pdo->prepare($maleQuery);
$stmt->execute();
$maleCount = $stmt->fetch(PDO::FETCH_ASSOC)['male_count'];

// Query to get count of female students by joining Students and Student_Admissions tables
$femaleQuery = "SELECT COUNT(*) AS female_count 
                FROM Students s 
                JOIN Student_Admissions sa ON s.id = sa.id 
                WHERE sa.student_gender = 'Female'";
$stmt = $pdo->prepare($femaleQuery);
$stmt->execute();
$femaleCount = $stmt->fetch(PDO::FETCH_ASSOC)['female_count'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Gender Pie Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }

        .gender_widget {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 80%;
            max-width: 500px;
            margin-left: 710px;
            margin-top: 20px;
        }

        .gender_widget h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        canvas {
            max-width: 100%;
            height: auto;
        }

    </style>
</head>
<body>

<div class="gender_widget">
    <h3>Student Gender Distribution</h3>
    <canvas id="genderPieChart"></canvas>
</div>

<script>
    // Data from PHP
    var maleCount = <?= $maleCount ?>;
    var femaleCount = <?= $femaleCount ?>;

    // Pie chart configuration
    var ctx = document.getElementById('genderPieChart').getContext('2d');
    var genderPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{
                label: 'Gender Distribution',
                data: [maleCount, femaleCount],
                backgroundColor: ['#4CAF50', '#FF5733'],
                borderColor: ['#fff', '#fff'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw;
                        }
                    }
                }
            }
        }
    });
</script>

</body>
</html>

<?php
// Database connection details
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'reliance';

// Create a connection
$conn = mysqli_connect($host, $username, $password, $dbname); 

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

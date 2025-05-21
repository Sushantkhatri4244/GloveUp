<?php
// Database configuration - modify these with your actual database credentials
$servername = "localhost:3306";
$username = "root"; // your database username
$password = "11111111"; // your database password
$dbname = "gloveup"; // your database name

try {
    // Create database connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "connected successfully";
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}


?>
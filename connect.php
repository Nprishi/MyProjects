<?php
$servername = "localhost"; // Database server name
$username = "root"; // Database username
$password = ""; // Database password (change this for production)
$dbname = "test"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set to UTF-8
$conn->set_charset("utf8");

// Enable error reporting (for development purposes)
// Turn this off in production for security reasons
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

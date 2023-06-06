<?php
// Database configuration
$servername = "localhost";
$username1 = "root";
$password1 = "root";
$database = "rating_proj";

// Create a connection
$conn = mysqli_connect($servername, $username1, $password1, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

<?php
$servername = "localhost";
$username = "root";      // default XAMPP user
$password = "";          // leave empty unless you’ve set one
$dbname = "gdclit_tracker";  // your database name

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>

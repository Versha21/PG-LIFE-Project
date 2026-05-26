<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pg_life"; // Update this to your actual database name if different

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
  die(json_encode(array("success" => false, "message" => "Database connection failed: " . mysqli_connect_error())));
}

mysqli_set_charset($conn, "utf8mb4");

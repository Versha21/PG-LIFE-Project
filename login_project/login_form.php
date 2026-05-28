<?php
session_start();
require_once __DIR__ . '/../includes/database_connect.php';

// Validate and sanitize inputs
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validate required fields
if (empty($email) || empty($password)) {
    echo json_encode(array("success" => false, "message" => "Email and password are required!"));
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array("success" => false, "message" => "Invalid email format!"));
    exit;
}

$password_hashed = sha1($password);

// Check user credentials using prepared statement
$sql = "SELECT * FROM users WHERE email = ? AND password = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $email, $password_hashed);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    echo json_encode(array("success" => false, "message" => "Database error!"));
    exit;
}

if (mysqli_num_rows($result) == 0) {
    echo json_encode(array("success" => false, "message" => "Login failed! Invalid email or password."));
    exit;
}

$row = mysqli_fetch_assoc($result);
$_SESSION['user_id'] = $row['id'];
$_SESSION['full_name'] = $row['full_name'];
$_SESSION['email'] = $row['email'];

echo json_encode(array("success" => true, "message" => "Login successful!"));
mysqli_close($conn);

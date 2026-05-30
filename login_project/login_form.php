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

$password_hashed = sha1($password); // keep for legacy fallback

// Fetch user by email and verify password (support upgrade from sha1)
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(array("success" => false, "message" => "Login failed! Invalid email or password."));
    exit;
}

$row = mysqli_fetch_assoc($result);

// Verify with password_verify first (new hashes)
$stored = isset($row['password']) ? $row['password'] : '';
$verified = false;
if (!empty($stored) && password_verify($password, $stored)) {
    $verified = true;
} elseif (!empty($stored) && $password_hashed === $stored) {
    // Legacy SHA1 match — upgrade to password_hash
    $verified = true;
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    $sql_up = "UPDATE users SET password = ? WHERE id = ?";
    $stmt_up = mysqli_prepare($conn, $sql_up);
    if ($stmt_up) {
        mysqli_stmt_bind_param($stmt_up, "si", $new_hash, $row['id']);
        mysqli_stmt_execute($stmt_up);
    }
}

if (!$verified) {
    echo json_encode(array("success" => false, "message" => "Login failed! Invalid email or password."));
    exit;
}

// continue with session creation


$_SESSION['user_id'] = $row['id'];
$_SESSION['full_name'] = $row['full_name'];
$_SESSION['email'] = $row['email'];

echo json_encode(array("success" => true, "message" => "Login successful!"));
mysqli_close($conn);

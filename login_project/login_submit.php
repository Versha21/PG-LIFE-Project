<?php
require_once __DIR__ . '/../includes/database_connect.php';

// Validate and sanitize inputs
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$college_name = isset($_POST['college_name']) ? trim($_POST['college_name']) : '';
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';

// Validate required fields
if (empty($full_name) || empty($phone) || empty($email) || empty($password) || empty($college_name) || empty($gender)) {
    echo json_encode(array("success" => false, "message" => "All fields are required!"));
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array("success" => false, "message" => "Invalid email format!"));
    exit;
}

// Validate phone number (basic validation)
if (!preg_match('/^[0-9]{10}$/', $phone)) {
    echo json_encode(array("success" => false, "message" => "Phone number must be 10 digits!"));
    exit;
}

// Use modern password hashing
$password_hashed = password_hash($password, PASSWORD_DEFAULT);

// Check if email already exists using prepared statement
$sql_check = "SELECT * FROM users WHERE email = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "s", $email);
mysqli_stmt_execute($stmt_check);
$result = mysqli_stmt_get_result($stmt_check);

if (!$result) {
    echo json_encode(array("success" => false, "message" => "Database error!"));
    exit;
}

if (mysqli_num_rows($result) != 0) {
    echo json_encode(array("success" => false, "message" => "This email id is already registered with us!"));
    exit;
}

// Insert new user using prepared statement
$sql_insert = "INSERT INTO users (email, password, full_name, phone, gender, college_name) VALUES (?, ?, ?, ?, ?, ?)";
$stmt_insert = mysqli_prepare($conn, $sql_insert);
mysqli_stmt_bind_param($stmt_insert, "ssssss", $email, $password_hashed, $full_name, $phone, $gender, $college_name);
$insert_result = mysqli_stmt_execute($stmt_insert);

if (!$insert_result) {
    echo json_encode(array("success" => false, "message" => "Failed to create account!"));
    exit;
}

echo json_encode(array("success" => true, "message" => "Your account has been created successfully!"));
mysqli_close($conn);

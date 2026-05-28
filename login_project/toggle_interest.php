<?php
session_start();

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/database_connect.php';

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
$is_logged_in = !is_null($user_id);

// Get property_id from GET request
$property_id = isset($_GET['property_id']) ? intval($_GET['property_id']) : NULL;

if (!$property_id) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid property ID",
        "is_logged_in" => $is_logged_in
    ));
    exit;
}

if (!$is_logged_in) {
    echo json_encode(array(
        "success" => false,
        "message" => "User not logged in",
        "is_logged_in" => false,
        "property_id" => $property_id
    ));
    exit;
}

// Check if user is already interested in this property
$sql_check = "SELECT * FROM interested_users_properties WHERE user_id = ? AND property_id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $property_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

$is_interested = mysqli_num_rows($result_check) > 0;

if ($is_interested) {
    // Remove interest
    $sql_delete = "DELETE FROM interested_users_properties WHERE user_id = ? AND property_id = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "ii", $user_id, $property_id);
    $delete_result = mysqli_stmt_execute($stmt_delete);

    if (!$delete_result) {
        echo json_encode(array(
            "success" => false,
            "message" => "Failed to remove interest",
            "is_logged_in" => $is_logged_in
        ));
        exit;
    }
    $is_interested = false;
} else {
    // Add interest
    $sql_insert = "INSERT INTO interested_users_properties (user_id, property_id) VALUES (?, ?)";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "ii", $user_id, $property_id);
    $insert_result = mysqli_stmt_execute($stmt_insert);

    if (!$insert_result) {
        echo json_encode(array(
            "success" => false,
            "message" => "Failed to add interest",
            "is_logged_in" => $is_logged_in
        ));
        exit;
    }
    $is_interested = true;
}

echo json_encode(array(
    "success" => true,
    "property_id" => $property_id,
    "is_interested" => $is_interested,
    "is_logged_in" => $is_logged_in
));
mysqli_close($conn);

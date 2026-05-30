<?php
session_start();

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require "../includes/database_connect.php";

// $conn is defined in database_connect.php

// Check database connection
if (!isset($conn) || !$conn) {
    echo json_encode(array("error" => "Database connection failed"));
    exit;
}

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : NULL;

// Validate city_name input
$city_name = isset($_GET["city"]) ? trim($_GET["city"]) : '';
if (empty($city_name)) {
    echo json_encode(array("error" => "City name is required"));
    exit;
}

// Sanitize city_name to prevent issues
$city_name = htmlspecialchars($city_name, ENT_QUOTES, 'UTF-8');

// Get city using prepared statement
$sql_1 = "SELECT * FROM cities WHERE name = ?";
$stmt_1 = mysqli_prepare($conn, $sql_1);
mysqli_stmt_bind_param($stmt_1, "s", $city_name);
mysqli_stmt_execute($stmt_1);
$result_1 = mysqli_stmt_get_result($stmt_1);

if (!$result_1) {
    echo json_encode(array("error" => "Database query failed"));
    exit;
}

$city = mysqli_fetch_assoc($result_1);
if (!$city) {
    echo json_encode(array("error" => "Sorry! We do not have any PG listed in this city."));
    exit;
}
$city_id = intval($city['id']);

// Get properties for the city using prepared statement
$sql_2 = "SELECT * FROM properties WHERE city_id = ?";
$stmt_2 = mysqli_prepare($conn, $sql_2);
mysqli_stmt_bind_param($stmt_2, "i", $city_id);
mysqli_stmt_execute($stmt_2);
$result_2 = mysqli_stmt_get_result($stmt_2);

if (!$result_2) {
    echo json_encode(array("error" => "Database query failed"));
    exit;
}
$properties = mysqli_fetch_all($result_2, MYSQLI_ASSOC);

// Get interested users for properties in this city using prepared statement
$sql_3 = "SELECT *
            FROM interested_users_properties iup
            INNER JOIN properties p ON iup.property_id = p.id
            WHERE p.city_id = ?";
$stmt_3 = mysqli_prepare($conn, $sql_3);
mysqli_stmt_bind_param($stmt_3, "i", $city_id);
mysqli_stmt_execute($stmt_3);
$result_3 = mysqli_stmt_get_result($stmt_3);

if (!$result_3) {
    echo json_encode(array("error" => "Database query failed"));
    exit;
}
$interested_users_properties = mysqli_fetch_all($result_3, MYSQLI_ASSOC);

// Build property list with additional data
$new_properties = array();
foreach ($properties as $property) {
    $property_id = intval($property['id']);
    $property_images = glob("../img/properties/" . $property_id . "/*");

    if (!empty($property_images)) {
        $property_image = "img/properties/" . $property_id . "/" . basename($property_images[0]);
    } else {
        $property_image = "img/logo.png"; // Fallback image
    }

    $interested_users_count = 0;
    $is_interested = false;
    foreach ($interested_users_properties as $interested_user_property) {
        if (intval($interested_user_property['property_id']) == $property_id) {
            $interested_users_count++;

            if ($user_id && intval($interested_user_property['user_id']) == $user_id) {
                $is_interested = true;
            }
        }
    }
    $property['interested_users_count'] = $interested_users_count;
    $property['is_interested'] = $is_interested;
    $property['image'] = $property_image;
    $new_properties[] = $property;
}

echo json_encode($new_properties);
mysqli_close($conn);

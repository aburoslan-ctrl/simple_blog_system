<?php
$method = "GET";
$cache  = "no-cache";
include "../../../head.php";

// Validate token once
$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
    exit;
}

// Admin only
if (!isset($user_id) || $user_id !== "admin") {
    respondUnauthorized("You are not authorized to view all users.");
    exit;
}

$stmt = $connect->prepare("
    SELECT 
        *
    FROM users
    ORDER BY id DESC
");

$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

respondOK(
    [
        "users" => $users,
        "total" => count($users)
    ],
    "Users fetched successfully."
);

$stmt->close();
?>

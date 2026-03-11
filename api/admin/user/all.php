<?php
$method = "GET";
$cache  = "no-cache";
include "../../../head.php";

// Validate token once
$user = ValidateAPITokenSentIN();

if (!isset($user->usertoken) || input_is_invalid($user->usertoken) || !is_numeric($user->usertoken)) {
    respondUnauthorized();
    exit;
}

// Admin only
// if (!isset($user->role) || $user->role !== "admin") {
//     respondUnauthorized("You are not authorized to view all users.");
//     exit;
// }

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

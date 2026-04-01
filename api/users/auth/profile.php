<?php

$method = "GET";
$cache  = "no-cache";
include "../../head.php";

// Validate token
$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized("Access token invalid or not sent.");
    exit;
}

// Fetch user profile
$stmt = $connect->prepare("
    SELECT id, username, email, role, created_at
    FROM users
    WHERE id = ? AND role = 'user'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
    respondOK(["user" => $profile], "Profile fetched successfully.");
} else {
    respondBadRequest("User not found.");
    exit;
}

?>
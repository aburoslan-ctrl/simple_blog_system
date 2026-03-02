<?php

$method = "POST";
$cache  = "no-cache";
include "../../head.php";

// Validate token once

    $datasentin=ValidateAPITokenSentIN();
    $user_id=$datasentin->usertoken;

if (!isset($user_id) || input_is_invalid($user_id)) {
    respondUnauthorized("Access token invalid or not sent.");
    exit;
}


// Ensure at least one field is sent
if (!isset($_POST['username']) && !isset($_POST['email']) && !isset($_POST['password'])) {
    respondBadRequest("Nothing to update. Provide username, email, or password.");
    exit;
}

// Fetch current user data
$curr = $connect->prepare("SELECT username, email, password FROM users WHERE id = ?");
$curr->bind_param("i", $user_id);
$curr->execute();
$current = $curr->get_result()->fetch_assoc();

if (!$current) {
    respondBadRequest("User not found.");
    exit;
}

// Get updated values or keep current
$username = isset($_POST['username']) ? cleanme($_POST['username']) : $current['username'];
$email    = isset($_POST['email'])    ? cleanme($_POST['email'])    : $current['email'];
$password = isset($_POST['password']) ? cleanme($_POST['password']) : $current['password'];

// Validate inputs
if (input_is_invalid($username) || input_is_invalid($email) || input_is_invalid($password)) {
    respondBadRequest("Fields cannot be empty.");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondBadRequest("Invalid email format.");
    exit;
}

// Check for conflicts with other users
$check = $connect->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
$check->bind_param("ssi", $email, $username, $user_id);
$check->execute();
$conflict = $check->get_result();

if ($conflict->num_rows > 0) {
    respondBadRequest("Email or username is already taken by another user.");
    exit;
}

// Update user profile
$update = $connect->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
$update->bind_param("sssi", $username, $email, $password, $user_id);
$update->execute();

if ($update->affected_rows > 0) {
    respondOK([], "Profile updated successfully.");
} else {
    respondBadRequest("No changes made or update failed.");
    exit;
}

?>
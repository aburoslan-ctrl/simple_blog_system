<?php

$method = "POST";
$cache  = "no-cache";
include "../head.php";

// Validate token
$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized("Access token invalid or not sent.");
    exit;
}

// Require password confirmation
if (!isset($_POST['password'])) {
    respondBadRequest("Password is required to delete your account.");
    exit;
}

$password = cleanme($_POST['password']);

if (input_is_invalid($password)) {
    respondBadRequest("Password is required to delete your account.");
    exit;
}

// Verify user exists and password is correct
$stmt = $connect->prepare("SELECT id, password FROM users WHERE id = ? AND role = 'user'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    respondBadRequest("User not found.");
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password']) && $password !== $user['password']) {
    respondBadRequest("Incorrect password.");
    exit;
}

// Delete user
$delete = $connect->prepare("DELETE FROM users WHERE id = ?");
$delete->bind_param("i", $user_id);
$delete->execute();

if ($delete->affected_rows > 0) {
    respondOK([], "Account deleted successfully.");
} else {
    respondInternalError("Failed to delete account. Please try again.");
}

?>
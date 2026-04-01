<?php

$method = "POST";
$cache  = "no-cache";
include "../../../head.php";

// Validate token
$user = ValidateAPITokenSentIN();
$admin_id = $user->usertoken;

if (!isset($admin_id) || input_is_invalid($admin_id) || !is_numeric($admin_id)) {
    respondUnauthorized();
    exit;
}

// Admin only
$roleCheck = $connect->prepare("SELECT role FROM users WHERE id = ?");
$roleCheck->bind_param("i", $admin_id);
$roleCheck->execute();
$roleResult = $roleCheck->get_result()->fetch_assoc();

if (!$roleResult || $roleResult['role'] !== 'admin') {
    respondForbiddenAuthorized("Admin access required.");
    exit;
}

if (!isset($_POST['user_id'])) {
    respondBadRequest("User ID is required.");
    exit;
}

$target_user_id = cleanme($_POST['user_id']);

if (input_is_invalid($target_user_id) || !is_numeric($target_user_id)) {
    respondBadRequest("A valid user ID is required.");
    exit;
}

$target_user_id = (int)$target_user_id;

// Fetch current user data
$curr = $connect->prepare("SELECT username, email, role FROM users WHERE id = ?");
$curr->bind_param("i", $target_user_id);
$curr->execute();
$current = $curr->get_result()->fetch_assoc();

if (!$current) {
    respondBadRequest("User not found.");
    exit;
}

// At least one field must be provided
if (!isset($_POST['username']) && !isset($_POST['email']) && !isset($_POST['role'])) {
    respondBadRequest("Nothing to update. Provide username, email, or role.");
    exit;
}

$username = isset($_POST['username']) ? cleanme($_POST['username']) : $current['username'];
$email    = isset($_POST['email'])    ? cleanme($_POST['email'])    : $current['email'];
$role     = isset($_POST['role'])     ? cleanme($_POST['role'])     : $current['role'];

if (input_is_invalid($username) || input_is_invalid($email) || input_is_invalid($role)) {
    respondBadRequest("Fields cannot be empty.");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondBadRequest("Invalid email format.");
    exit;
}

// Validate role
$allowedRoles = ["user", "admin"];
if (!in_array($role, $allowedRoles, true)) {
    respondBadRequest("Invalid role. Use 'user' or 'admin'.");
    exit;
}

// Check for conflicts with other users
$check = $connect->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
$check->bind_param("ssi", $email, $username, $target_user_id);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    respondBadRequest("Email or username is already taken by another user.");
    exit;
}

$stmt = $connect->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
$stmt->bind_param("sssi", $username, $email, $role, $target_user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    respondOK([
        "user_id"  => $target_user_id,
        "username" => $username,
        "email"    => $email,
        "role"     => $role
    ], "User updated successfully.");
} else {
    respondBadRequest("No changes made or update failed.");
}

?>

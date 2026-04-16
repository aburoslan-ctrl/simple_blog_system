<?php
$method = "POST";
$cache  = "no-cache";
include "../../../head.php";

if (!isset($_POST['token']) || !isset($_POST['password'])) {
    respondBadRequest("Token and password are required.");
}

$token    = cleanme($_POST['token']);
$password = cleanme($_POST['password']);

if (input_is_invalid($token) || input_is_invalid($password)) {
    respondBadRequest("Token and password are required.");
} elseif (!validatePassword($password)) {
    respondBadRequest("Password must be at least 6 characters with uppercase, lowercase, number and special character.");
}

$stmt = $connect->prepare(
    "SELECT id FROM users
     WHERE reset_token = ? AND reset_expires_at IS NOT NULL AND reset_expires_at > NOW()"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    respondBadRequest("Invalid or expired token.");
}

$user   = $result->fetch_assoc();
$userId = $user['id'];

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$update = $connect->prepare(
    "UPDATE users
     SET password = ?, reset_token = NULL, reset_expires_at = NULL
     WHERE id = ?"
);
$update->bind_param("si", $hashedPassword, $userId);

if ($update->execute()) {
    respondOK([], "Password has been reset. Please log in.");
} else {
    respondInternalError("Password reset failed. Please try again.");
}


?>

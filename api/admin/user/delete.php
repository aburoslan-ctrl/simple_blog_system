<?php

$method = "POST";
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
    respondUnauthorized("You are not authorized to delete users.");
    exit;
}

if (isset($_POST['id'])) {

    $user_id = cleanme($_POST['id']);

    if (input_is_invalid($user_id)) {
        respondBadRequest("User ID is required.");
        exit;
    } else if (!is_numeric($user_id)) {
        respondBadRequest("User ID must be numeric.");
        exit;
    }

    $user_id = (int)$user_id;

    // Check if user exists
    $checkUser = $connect->prepare("SELECT id FROM users WHERE id = ?");
    $checkUser->bind_param("i", $user_id);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("User not found.");
        exit;
    }

    // Delete user (related data should cascade if FK exists)
    $deleteUser = $connect->prepare("DELETE FROM users WHERE id = ?");
    $deleteUser->bind_param("i", $user_id);
    $deleteUser->execute();

    if ($deleteUser->affected_rows > 0) {
        respondOK([], "User deleted successfully.");
    } else {
        respondBadRequest("No changes made or delete failed.");
    }

} else {
    respondBadRequest("Invalid request. User ID is required.");
}

?>

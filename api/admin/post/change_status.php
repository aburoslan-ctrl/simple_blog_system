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

// // Admin only
if (!isset($user_id) || $user_id !== "admin") {
    respondUnauthorized("You are not authorized to change post status.");
    exit;
}

if (!isset($_POST['id']) || !isset($_POST['status'])) {
    respondBadRequest("Post ID and status are required.");
    exit;
}

$post_id = cleanme($_POST['id']);
$status  = cleanme($_POST['status']);

if (input_is_invalid($post_id) || input_is_invalid($status)) {
    respondBadRequest("Post ID and status cannot be empty.");
    exit;
}

if (!is_numeric($post_id)) {
    respondBadRequest("Post ID must be numeric.");
    exit;
}elseif (strlen($status) < 3) {
    respondBadRequest("Status is too short.");
    exit;
}elseif (strlen($status) > 20) {
    respondBadRequest("Status is too long.");
    exit;
}

$allowed = ["draft", "published"];
if (!in_array($status, $allowed, true)) {
    respondBadRequest("Invalid status. Use 'draft' or 'published'.");
    exit;
}

$post_id = (int)$post_id;

// Ensure post exists
$checkPost = $connect->prepare("SELECT id FROM posts WHERE id = ?");
$checkPost->bind_param("i", $post_id);
$checkPost->execute();
$result = $checkPost->get_result();

if ($result->num_rows == 0) {
    respondBadRequest("Post not found.");
    exit;
}

// Update status
$update = $connect->prepare("UPDATE posts SET status = ? WHERE id = ?");
$update->bind_param("si", $status, $post_id);
$update->execute();

if ($update->affected_rows > 0) {
    respondOK([], "Post status updated successfully.");
} else {
    respondBadRequest("No changes made or update failed.");
}

?>

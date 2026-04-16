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

if (isset($_POST['id'])) {

    $post_id = cleanme($_POST['id']);

    // Validation
    if (input_is_invalid($post_id)) {
        respondBadRequest("Post ID is required.");
        exit;
    }

    else if (!is_numeric($post_id)) {
        respondBadRequest("Post ID must be numeric.");
        exit;
    }

    $post_id = (int)$post_id;

    // Check if post exists
    $checkPost = $connect->prepare("SELECT id, user_id FROM posts WHERE id = ?");
    $checkPost->bind_param("i", $post_id);
    $checkPost->execute();
    $result = $checkPost->get_result();

    if ($result->num_rows == 0) {
        respondBadRequest("Post not found.");
        exit;
    }

    $post = $result->fetch_assoc();

    // Authorization: must be the post author or an admin
    $roleStmt = $connect->prepare("SELECT role FROM users WHERE id = ?");
    $roleStmt->bind_param("i", $user_id);
    $roleStmt->execute();
    $roleRow = $roleStmt->get_result()->fetch_assoc();
    $isAdmin = ($roleRow && $roleRow['role'] === 'admin');
    if ((int)$post['user_id'] !== (int)$user_id && !$isAdmin) {
        respondForbiddenAuthorized("You are not authorized to delete this post.");
        exit;
    }

    $deleteComments = $connect->prepare("DELETE FROM comments WHERE post_id = ?");
    $deleteComments->bind_param("i", $post_id);
    $deleteComments->execute();

    // Delete post
    $deletePost = $connect->prepare("DELETE FROM posts WHERE id = ?");
    $deletePost->bind_param("i", $post_id);
    $deletePost->execute();

    if ($deletePost->affected_rows > 0) {
        respondOK([], "Post deleted successfully.");
    } else {
        respondBadRequest("No changes made or delete failed.");
    }

} else {
    respondBadRequest("Invalid request. Post ID is required.");
}

?>
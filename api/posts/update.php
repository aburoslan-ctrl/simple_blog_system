
<?php

$method = "POST";
$cache  = "no-cache";
include "../../head.php";

// Validate token once
$user = ValidateAPITokenSentIN();

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

    // Authorization (author or admin only)
   if ($post['user_id'] != $user->usertoken && $user->role != "admin") {
       respondUnauthorized("You are not authorized to update this post.");
       exit;
   }

    // Fetch current post data
    $getCurrent = $connect->prepare("SELECT title, content FROM posts WHERE id = ?");
    $getCurrent->bind_param("i", $post_id);
    $getCurrent->execute();
    $current = $getCurrent->get_result()->fetch_assoc();

    $title   = isset($_POST['title'])   ? cleanme($_POST['title'])   : $current['title'];
    $content = isset($_POST['content']) ? cleanme($_POST['content']) : $current['content'];

    if (input_is_invalid($title) || input_is_invalid($content)) {
        respondBadRequest("Title and content cannot be empty.");
        exit;
    } elseif (strlen($title) < 3) {
        respondBadRequest("Title is too short.");
        exit;
    } elseif (strlen($title) > 255) {
        respondBadRequest("Title is too long.");
        exit;
    } elseif (strlen($content) < 10) {
        respondBadRequest("Content is too short.");
        exit;
    } elseif (strlen($content) > 5000) {
        respondBadRequest("Content is too long.");
        exit;   
    }

    // Update post
    $updatePost = $connect->prepare("
        UPDATE posts 
        SET title = ?, content = ?
        WHERE id = ?
    ");

    $updatePost->bind_param("ssi", $title, $content, $post_id);
    $updatePost->execute();

    if ($updatePost->affected_rows > 0) {

        respondOK([], "Post updated successfully.");

    } else {
        respondBadRequest("No changes made or update failed.");
    }

} else {
    respondBadRequest("Invalid request. Post ID is required.");
}

?>
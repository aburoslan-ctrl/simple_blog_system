<?php

$method = "GET";
$cache  = "no-cache";
include "../../head.php";

//  Validate token first 
$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
}

$user_id = (int)$user_id;

//  Validate post_id
if (!isset($_GET['post_id']) || input_is_invalid($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    respondBadRequest("A valid post ID is required.");
}

$post_id = (int) $_GET['post_id'];

//  Ensure post exists
$checkPost = $connect->prepare("SELECT id FROM posts WHERE id = ?");
$checkPost->bind_param("i", $post_id);
$checkPost->execute();
$checkResult = $checkPost->get_result();

if ($checkResult->num_rows === 0) {
    respondBadRequest("Post not found.");
}

// Fetch comments
$stmt = $connect->prepare("
    SELECT
        c.id,
        c.post_id,
        c.user_id,
        c.comment,
        c.created_at,
        u.username AS author
    FROM comments c
    JOIN users u ON u.id = c.user_id
    WHERE c.post_id = ?
    ORDER BY c.created_at DESC
");

$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];

while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

respondOK(
    [
        "comments" => $comments,
        "total" => count($comments)
    ],
    "Comments fetched successfully."
);

$stmt->close();
$checkPost->close();
<?php
$method = "GET";
$cache  = "no-cache";
include "../../head.php";

// Validate token once
$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
    exit;
}
$user_id = (int)$user_id;

// Fetch all posts with full table columns
$stmt = $connect->prepare("
    SELECT 
        p.id,
        p.user_id,
        p.title,
        p.slug,
        p.content,
        p.image,
        p.status,
        p.created_at,
        p.updated_at,
        u.username AS author
    FROM posts p
    JOIN users u ON u.id = p.user_id
    ORDER BY p.updated_at DESC
");

$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
}

respondOK(
    [
        "posts" => $posts,
        "total" => count($posts)
    ],
    "Posts fetched successfully."
);

$stmt->close();
?>

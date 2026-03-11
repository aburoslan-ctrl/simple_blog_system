<?php
$method = "GET";
$cache  = "no-cache";
include "../../../head.php";

// Validate token once
$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id  )) {
    respondUnauthorized();
    exit;
}

// Admin only
if (!isset($user_id) || $user_id !== "admin") {
    respondUnauthorized("You are not authorized to view all posts.");
    exit;
}

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

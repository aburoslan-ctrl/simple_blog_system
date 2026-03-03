<?php

$method = "GET";
$cache  = "no-cache";
include "../../head.php";

// Validate token once
$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
}

$user_id = (int)$user_id;

// Prepare query
$stmt = $connect->prepare("
    SELECT 
        p.id,
        p.title,
        p.content,
        p.created_at,
        u.username AS author,
        GROUP_CONCAT(c.name ORDER BY c.name ASC SEPARATOR ', ') AS categories
    FROM posts p
    JOIN users u ON u.id = p.user_id
    LEFT JOIN post_categories pc ON pc.post_id = p.id
    LEFT JOIN categories c ON c.id = pc.category_id
    GROUP BY p.id
    ORDER BY p.created_at DESC
");

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    $posts = [];

    while ($row = $result->fetch_assoc()) {
        $row['categories'] = $row['categories'] 
            ? explode(", ", $row['categories']) 
            : [];
        $posts[] = $row;
    }

    respondOK([
        "posts" => $posts,
        "total" => count($posts)
    ], "Posts fetched successfully.");

} else {

    respondOK([
        "posts" => [],
        "total" => 0
    ], "No posts found.");

}

$stmt->close();
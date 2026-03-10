<?php
$method = "GET";
$cache  = "no-cache";
include "../../head.php";

/* VALIDATE TOKEN */
$datasentin = ValidateAPITokenSentIN();
$user_id = $datasentin->usertoken;

/* VALIDATE USER ID */
if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized("Unauthorized access.");
}

$user_id = (int)$user_id;

/* PREPARE QUERY */
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
        p.updated_at
    FROM posts p
    ORDER BY p.id DESC
");

if (!$stmt) {
    respondBadRequest("Failed to prepare query.");
}

/* EXECUTE QUERY */
$stmt->execute();
$result = $stmt->get_result();

/* PROCESS RESULTS */
if ($result->num_rows > 0) {

    $posts = [];

    while ($row = $result->fetch_assoc()) {
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

/* CLOSE STATEMENT */
$stmt->close();
?>
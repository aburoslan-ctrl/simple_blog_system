<?php

$method = "GET";
$cache  = "no-cache";
include "../../../head.php";

// Validate token
$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
    exit;
}

// Admin only
$roleCheck = $connect->prepare("SELECT role FROM users WHERE id = ?");
$roleCheck->bind_param("i", $user_id);
$roleCheck->execute();
$roleResult = $roleCheck->get_result()->fetch_assoc();

if (!$roleResult || $roleResult['role'] !== 'admin') {
    respondForbiddenAuthorized("Admin access required.");
    exit;
}

// Total users
$users = $connect->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];

// Total posts
$posts = $connect->query("SELECT COUNT(*) as count FROM posts")->fetch_assoc()['count'];

// Published posts
$published = $connect->query("SELECT COUNT(*) as count FROM posts WHERE status = 'published'")->fetch_assoc()['count'];

// Draft posts
$drafts = $connect->query("SELECT COUNT(*) as count FROM posts WHERE status = 'draft'")->fetch_assoc()['count'];

// Total comments
$comments = $connect->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];

// Total categories
$categories = $connect->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];

respondOK([
    "total_users"      => (int)$users,
    "total_posts"      => (int)$posts,
    "published_posts"  => (int)$published,
    "draft_posts"      => (int)$drafts,
    "total_comments"   => (int)$comments,
    "total_categories" => (int)$categories
], "Dashboard stats fetched successfully.");

?>

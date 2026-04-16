<?php

$method = "POST";
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

if (!isset($_POST['id'])) {
    respondBadRequest("Post ID is required.");
    exit;
}

$post_id = cleanme($_POST['id']);

if (input_is_invalid($post_id) || !is_numeric($post_id)) {
    respondBadRequest("A valid Post ID is required.");
    exit;
}

$post_id = (int)$post_id;

// Check post exists and get current data
$getCurrent = $connect->prepare("SELECT title, content, image FROM posts WHERE id = ?");
$getCurrent->bind_param("i", $post_id);
$getCurrent->execute();
$current = $getCurrent->get_result()->fetch_assoc();

if (!$current) {
    respondBadRequest("Post not found.");
    exit;
}

$title   = isset($_POST['title']) ? cleanme($_POST['title']) : $current['title'];
$content = isset($_POST['content']) ? cleanme($_POST['content']) : $current['content'];
$imagePath = $current['image'];

if (input_is_invalid($title) || input_is_invalid($content)) {
    respondBadRequest("Title and content cannot be empty.");
    exit;
}

// Optional image upload
if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['image'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        respondBadRequest("Image upload failed.");
        exit;
    }

    $maxSize = 2 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        respondBadRequest("Image is too large. Max 2MB.");
        exit;
    }

    $allowedExt = ["jpg", "jpeg", "png", "gif", "webp"];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        respondBadRequest("Invalid image type. Allowed: jpg, jpeg, png, gif, webp.");
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowedMime = ["image/jpeg", "image/png", "image/gif", "image/webp"];
    if (!in_array($mime, $allowedMime, true)) {
        respondBadRequest("Invalid image file.");
        exit;
    }

    $uploadDir = dirname(__DIR__, 2) . "/uploads/posts";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid("post_", true) . "." . $ext;
    $targetPath = $uploadDir . "/" . $filename;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        respondBadRequest("Failed to save image.");
        exit;
    }

    // Delete old image
    if ($current['image']) {
        $oldImagePath = dirname(__DIR__, 2) . "/" . $current['image'];
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
    }

    $imagePath = "uploads/posts/" . $filename;
}

$update = $connect->prepare("UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ?");
$update->bind_param("sssi", $title, $content, $imagePath, $post_id);

if ($update->execute()) {

    // Update categories if provided
    if (isset($_POST['category_ids']) && !empty($_POST['category_ids'])) {
        $category_ids = $_POST['category_ids'];

        $delCats = $connect->prepare("DELETE FROM post_categories WHERE post_id = ?");
        $delCats->bind_param("i", $post_id);
        $delCats->execute();

        $ids = array_filter(array_map('trim', explode(",", $category_ids)), 'is_numeric');
        foreach ($ids as $cat_id) {
            $cat_id = (int)$cat_id;

            $chk = $connect->prepare("SELECT id FROM categories WHERE id = ?");
            $chk->bind_param("i", $cat_id);
            $chk->execute();
            if ($chk->get_result()->num_rows === 0) continue;

            $pc = $connect->prepare("INSERT IGNORE INTO post_categories (post_id, category_id) VALUES (?, ?)");
            $pc->bind_param("ii", $post_id, $cat_id);
            $pc->execute();
        }
    }

    respondOK(["post_id" => $post_id], "Post updated successfully.");
} else {
    respondBadRequest("Failed to update post.");
}

?>

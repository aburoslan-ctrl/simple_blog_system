<?php
$method = "POST";
$cache  = "no-cache";
include "../../../head.php";

$user = ValidateAPITokenSentIN();

if (isset($_POST['title']) && isset($_POST['content'])) {

    $content      = cleanme($_POST['content']);
    $title = cleanme($_POST['title']);
    $imagePath = null;

    
    $datasentin=ValidateAPITokenSentIN();
    $user_id=$datasentin->usertoken;

    if (input_is_invalid($title) || input_is_invalid($content)) {
        respondBadRequest("Title and content are required.");
    } else {

        // Optional image upload (multipart/form-data with field name "image")
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['image'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                respondBadRequest("Image upload failed.");
            }

            $maxSize = 2 * 1024 * 1024; // 2MB
            if ($file['size'] > $maxSize) {
                respondBadRequest("Image is too large. Max 2MB.");
            }

            $allowedExt = ["jpg", "jpeg", "png", "gif", "webp"];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) {
                respondBadRequest("Invalid image type. Allowed: jpg, jpeg, png, gif, webp.");
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);
            $allowedMime = ["image/jpeg", "image/png", "image/gif", "image/webp"];
            if (!in_array($mime, $allowedMime, true)) {
                respondBadRequest("Invalid image file.");
            }

            $uploadDir = dirname(__DIR__, 2) . "/uploads/posts";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = uniqid("post_", true) . "." . $ext;
            $targetPath = $uploadDir . "/" . $filename;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                respondBadRequest("Failed to save image.");
            }

            // Store relative path for API clients
            $imagePath = "uploads/posts/" . $filename;
        }

        $insert = $connect->prepare("INSERT INTO posts (user_id, title, content, image, created_at) VALUES (?, ?, ?, ?, NOW())");
        $insert->bind_param("isss", $user_id, $title, $content, $imagePath);

        if ($insert->execute()) {
            $post_id = $connect->insert_id;

            // Assign categories if provided (comma-separated e.g. "1,2,3")
            if (!empty($category_ids)) {
                $ids = array_filter(array_map('trim', explode(",", $category_ids)), 'is_numeric');
                foreach ($ids as $cat_id) {
                    $cat_id = (int)$cat_id;

                    $chk = $connect->prepare("SELECT id FROM categories WHERE id = ?");
                    $chk->bind_param("i", $cat_id);
                    $chk->execute();
                    if ($chk->get_result()->num_rows === 0) continue; // skip invalid

                    $pc = $connect->prepare("INSERT IGNORE INTO post_categories (post_id, category_id) VALUES (?, ?)");
                    $pc->bind_param("ii", $post_id, $cat_id);
                    $pc->execute();
                }
            }

            respondOK(["post_id" => $post_id], "Post created successfully.");
        } else {
            respondBadRequest("Failed to create post. Please try again.");
        }
    }

} else {
    respondBadRequest("Invalid request. Title and content are required.");
}
?>

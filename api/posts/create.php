<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();

if (isset($_POST['title']) && isset($_POST['content'])) {

    $content      = cleanme($_POST['content']);
    $title = cleanme($_POST['title']);

    
    $datasentin=ValidateAPITokenSentIN();
    $user_id=$datasentin->usertoken;

    if (input_is_invalid($title) || input_is_invalid($content)) {
        respondBadRequest("Title and content are required.");
    } else {

        $insert = $connect->prepare("INSERT INTO posts (user_id, title, content, created_at) VALUES (?, ?, ?, NOW())");
        $insert->bind_param("iss", $user_id, $title, $content);

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

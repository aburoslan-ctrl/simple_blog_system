<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();

if (isset($_POST['post_id']) && isset($_POST['comment'])) {

    $post_id = cleanme($_POST['post_id']);
    $content = cleanme($_POST['comment']);
    ///$user_id = cleanme($user['id']);
    
    $datasentin=ValidateAPITokenSentIN();
    $user_id=$datasentin->usertoken;

    if (input_is_invalid($post_id) || !is_numeric($post_id)) {
        respondBadRequest("A valid post ID is required.");
    } else if (input_is_invalid($content)) {
        respondBadRequest("Comment content cannot be empty.");
    } else {

        $post_id = (int)$post_id;

        // Check post exists
        $check = $connect->prepare("SELECT id FROM posts WHERE id = ?");
        $check->bind_param("i", $post_id);
        $check->execute();

        if ($check->get_result()->num_rows === 0) {
            respondBadRequest("The post you are trying to comment on does not exist.");
        } else {

            $insert = $connect->prepare("INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
            $insert->bind_param("iis", $post_id, $user_id, $content);

            if ($insert->execute()) {
                
                respondOK([],"Comment posted successfully.");
            } else {
                respondBadRequest("Failed to post comment. Please try again.");
            }
        }
    }

} else {
    respondBadRequest("Invalid request. post_id and content are required.");
}
?>

<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";


$user = ValidateAPITokenSentIN();

if (isset($_POST['id'])) {

    $comment_id = cleanme($_POST['id']);
    
    $datasentin=ValidateAPITokenSentIN();
    $user_id=$datasentin->usertoken;

    if (input_is_invalid($comment_id) || !is_numeric($comment_id)) {
        respondBadRequest("A valid comment ID is required.");
    } else {

        $comment_id = (int)$comment_id;

        $check = $connect->prepare("SELECT id, user_id FROM comments WHERE id = ?");
        $check->bind_param("i", $comment_id);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows === 0) {
            respondBadRequest("Comment not found.");
            exit;
        } 

            $comment = $result->fetch_assoc();

            // if ($comment['user_id'] !== $user['id'] && $user['role'] !== 'admin') {
            //     respondUnauthorized("You are not authorized to delete this comment.");
            // } else {

                $delete = $connect->prepare("DELETE FROM comments WHERE id = ?");
                $delete->bind_param("i", $comment_id);

                if ($delete->execute()) {
                    respondOK([], "Comment deleted successfully.");
                } else {
                    respondBadRequest("Failed to delete comment. Please try again.");
                }
            }
        }


else {
    respondBadRequest("Invalid request. Comment ID is required.");
}
?>

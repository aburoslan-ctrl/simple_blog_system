<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

$user = ValidateAPITokenSentIN();

if (isset($_POST['id']) && isset($_POST['comment'])) {

    $comment_id = cleanme($_POST['id']);
    $content    = cleanme($_POST['comment']);
    
    $datasentin=ValidateAPITokenSentIN();
    $user_id=$datasentin->usertoken;

    if (input_is_invalid($comment_id) || !is_numeric($comment_id)) {
        respondBadRequest("A valid comment ID is required.");
    } else if (input_is_invalid($content)) {
        respondBadRequest("Comment content cannot be empty.");
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

          
             

                $update = $connect->prepare("UPDATE comments SET comment = ? WHERE id = ?");
                $update->bind_param("si", $content, $comment_id);

                if ($update->execute()) {
                    respondOK([], "Comment updated successfully.");
                } else {
                    respondBadRequest("Failed to update comment. Please try again.");
                }
            }
        }
    

 else {
    respondBadRequest("Invalid request. Comment ID and content are required.");
}
?>

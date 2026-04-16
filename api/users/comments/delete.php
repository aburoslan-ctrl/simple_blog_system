<?php
$method = "POST";
$cache  = "no-cache";
include "../../../head.php";


$user = ValidateAPITokenSentIN();

if (isset($_POST['comment_id'])) {

    $comment_id = cleanme($_POST['comment_id']);

    $user_id = $user->usertoken;

    // Look up role from DB for admin check
    $roleStmt = $connect->prepare("SELECT role FROM users WHERE id = ?");
    $roleStmt->bind_param("i", $user_id);
    $roleStmt->execute();
    $roleRow = $roleStmt->get_result()->fetch_assoc();
    $isAdmin = ($roleRow && $roleRow['role'] === 'admin');

    if (input_is_invalid($comment_id) || !is_numeric($comment_id)) {
        respondBadRequest("A valid comment ID is required.");
    } else {

        $comment_id = (int)$comment_id;

        $check = $connect->prepare("SELECT id, user_id FROM comments WHERE id = ?");
        $check->bind_param("i", $comment_id);
        $check->execute();
        $result = $check->get_result();
// this is to check if the comment exists before trying to delete it, and also to fetch the user_id of the comment for authorization check
        if ($result->num_rows === 0) {
            respondBadRequest("Comment not found.");
            exit;
        } 

            $comment = $result->fetch_assoc();
//this is to check if the user is the owner of the comment or an admin before allowing deletion
            if ((int)$comment['user_id'] !== (int)$user_id && !$isAdmin){
                respondUnauthorized("You are not authorized to delete this comment.");
            }else {

                $delete = $connect->prepare("DELETE FROM comments WHERE id = ?");
                $delete->bind_param("i", $comment_id);

                if ($delete->execute()) {
                    respondOK([], "Comment deleted successfully.");
                } else {
                    respondBadRequest("Failed to delete comment. Please try again.");
                }
            
        }

    }
}

else {
    respondBadRequest("Invalid request. Comment ID is required.");
}
?>

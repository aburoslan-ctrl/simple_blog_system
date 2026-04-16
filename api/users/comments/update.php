<?php
$method = "POST";
$cache  = "no-cache";
include "../../../head.php";

$user = ValidateAPITokenSentIN();

if (isset($_POST['id']) && isset($_POST['comment'])) {

    $comment_id = cleanme($_POST['id']);
    $content    = cleanme($_POST['comment']);

    $user_id = $user->usertoken;

    if (input_is_invalid($comment_id) || !is_numeric($comment_id)) {
        respondBadRequest("A valid comment ID is required.");
    } else if (input_is_invalid($content)) {
        respondBadRequest("Comment content cannot be empty.");
    }   elseif (strlen($content) < 3) {
    respondBadRequest("Comment is too short.");
}
elseif (strlen($content) > 1000) {
    respondBadRequest("Comment is too long.");
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

            // Authorization: must be the comment author or an admin
            $roleStmt = $connect->prepare("SELECT role FROM users WHERE id = ?");
            $roleStmt->bind_param("i", $user_id);
            $roleStmt->execute();
            $roleRow = $roleStmt->get_result()->fetch_assoc();
            $isAdmin = ($roleRow && $roleRow['role'] === 'admin');
            if ((int)$comment['user_id'] !== (int)$user_id && !$isAdmin) {
                respondForbiddenAuthorized("You are not authorized to update this comment.");
                exit;
            }

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

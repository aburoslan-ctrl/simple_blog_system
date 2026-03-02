<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

if (isset($_POST['email']) && isset($_POST['password'])) {

    $email    = cleanme($_POST['email']);
    $password = cleanme($_POST['password']);

    if (input_is_invalid($email) || input_is_invalid($password)) {
        respondBadRequest("Email and password are required.");
    } else {

        $stmt = $connect->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user        = $result->fetch_assoc();
            $accessToken = getTokenToSendAPI($user['id']);

            respondOK([
                "access_token" => $accessToken,
                "user" => [
                    "username" => $user['username'],
                    "email"    => $user['email'],
                    "role"     => $user['role']
                ]
            ], "Login successful.");
        } else {
            respondBadRequest("Invalid email or password.");
        }
    }

} else {
    respondBadRequest("Invalid request. Email and password are required.");
}
?>

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

        $stmt = $connect->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user        = $result->fetch_assoc();
            $isValidPassword = false;

            if (password_verify($password, $user['password'])) {
                $isValidPassword = true;
            } elseif ($password === $user['password']) {
                // Backward compatibility for old plain-text passwords.
                $isValidPassword = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                if ($newHash !== false) {
                    $update = $connect->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $update->bind_param("si", $newHash, $user['id']);
                    $update->execute();
                }
            }

            if (!$isValidPassword) {
                respondBadRequest("Invalid email or password.");
            }

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

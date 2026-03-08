<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {

    $username = cleanme($_POST['username']);
    $email    = cleanme($_POST['email']);
    $password = cleanme($_POST['password']);
    
    $datasentin=ValidateAPITokenSentIN();
    $user_id=$datasentin->usertoken;

    if (input_is_invalid($username) || input_is_invalid($email) || input_is_invalid($password)) {
        respondBadRequest("Username, email, and password are required.");
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respondBadRequest("Invalid email format.");
    } else {

        // Check if email or username already exists
        $check = $connect->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->bind_param("ss", $email, $username);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            respondBadRequest("Email or username already taken.");
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            if ($passwordHash === false) {
                respondBadRequest("Unable to process password.");
            }

            $role   = "user";
            $insert = $connect->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $insert->bind_param("ssss", $username, $email, $passwordHash, $role);

            if ($insert->execute()) {
            
                respondOK([],"Registration successful.");
            } else {
                respondBadRequest("Registration failed. Please try again.");
            }
        }
    }

} else {
    respondBadRequest("Invalid request. Username, email, and password are required.");
}
?>

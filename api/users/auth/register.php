<?php
$method = "POST";
$cache  = "no-cache";
include "../../head.php";

if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {

    $username = cleanme($_POST['username']);
    $email    = cleanme($_POST['email']);
    $password = cleanme($_POST['password']);

    if (input_is_invalid($username) || input_is_invalid($email) || input_is_invalid($password)) {
        respondBadRequest("Username, email and password are required.");
    } elseif (!validateEmail($email)) {
        respondBadRequest("Invalid email format.");
    } elseif (!validatePassword($password)) {
        respondBadRequest("Password must be at least 6 characters with uppercase, lowercase, number and special character.");
    } else {

        // Check if email already exists
        $stmt = $connect->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            respondBadRequest("Email already registered.");
        }

        // Check if username already exists
        $stmt = $connect->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            respondBadRequest("Username already taken.");
        }

        // Hash password and insert user with role 'user'
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = "user";

        $stmt = $connect->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            $accessToken = getTokenToSendAPI($userId);

            respondOK([
                "access_token" => $accessToken,
                "user" => [
                    "id"       => $userId,
                    "username" => $username,
                    "email"    => $email,
                    "role"     => $role
                ]
            ], "Registration successful.");
        } else {
            respondInternalError("Registration failed. Please try again.");
        }
    }

} else {
    respondBadRequest("Invalid request. Username, email and password are required.");
}
?>

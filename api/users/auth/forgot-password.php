<?php
$method = "POST";
$cache  = "no-cache";
include "../../../head.php";

$genericMessage = "If the email exists, a reset link has been sent.";

if (!isset($_POST['email'])) {
    respondBadRequest("Email is required.");
}

$email = cleanme($_POST['email']);

if (input_is_invalid($email)) {
    respondBadRequest("Email is required.");
} elseif (!validateEmail($email)) {
    respondBadRequest("Invalid email format.");
}

$stmt = $connect->prepare("SELECT id, username FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    $token     = createUniqueToken(64, 'users', 'reset_token', 'pr_', true, true, true);
    $expiresAt = date('Y-m-d H:i:s', time() + (RESET_TOKEN_EXPIRY_MIN * 60));

    $update = $connect->prepare("UPDATE users SET reset_token = ?, reset_expires_at = ? WHERE id = ?");
    $update->bind_param("ssi", $token, $expiresAt, $user['id']);
    $update->execute();

    $body = "Hi " . $user['username'] . ",\n\n"
          . "We received a request to reset your password.\n\n"
          . "Use this token within " . RESET_TOKEN_EXPIRY_MIN . " minutes to reset it:\n\n"
          . $token . "\n\n"
          . "POST it along with your new password to /api/users/auth/reset-password.php\n"
          . "(fields: token, password)\n\n"
          . "If you did not request this, you can ignore this email.";

    try {
        $mail = new \SendGrid\Mail\Mail();
        $mail->setFrom(SENDGRID_FROM_EMAIL, SENDGRID_FROM_NAME);
        $mail->addTo($email, $user['username']);
        $mail->setSubject("Reset your password");
        $mail->addContent("text/plain", $body);

        $sg       = new \SendGrid(SENDGRID_API_KEY);
        $response = $sg->send($mail);

        if ($response->statusCode() >= 300) {
            error_log("SendGrid failed (" . $response->statusCode() . "): " . $response->body());
        }
    } catch (\Exception $e) {
        error_log("SendGrid exception: " . $e->getMessage());
    }
}

respondOK([], $genericMessage);
?>

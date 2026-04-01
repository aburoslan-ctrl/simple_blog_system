<?php

$method = "POST";
$cache  = "no-cache";
include "../../../head.php";

// Validate token
$user = ValidateAPITokenSentIN();
$user_id = $user->usertoken;

if (!isset($user_id) || input_is_invalid($user_id) || !is_numeric($user_id)) {
    respondUnauthorized();
    exit;
}

// Admin only
$roleCheck = $connect->prepare("SELECT role FROM users WHERE id = ?");
$roleCheck->bind_param("i", $user_id);
$roleCheck->execute();
$roleResult = $roleCheck->get_result()->fetch_assoc();

if (!$roleResult || $roleResult['role'] !== 'admin') {
    respondForbiddenAuthorized("Admin access required.");
    exit;
}

if (!isset($_POST['name'])) {
    respondBadRequest("Category name is required.");
    exit;
}

$name = cleanme($_POST['name']);

if (input_is_invalid($name)) {
    respondBadRequest("Category name cannot be empty.");
    exit;
}

if (strlen($name) < 2 || strlen($name) > 100) {
    respondBadRequest("Category name must be between 2 and 100 characters.");
    exit;
}

// Check for duplicate
$check = $connect->prepare("SELECT id FROM categories WHERE name = ?");
$check->bind_param("s", $name);
$check->execute();

if ($check->get_result()->num_rows > 0) {
    respondBadRequest("Category already exists.");
    exit;
}

$stmt = $connect->prepare("INSERT INTO categories (name) VALUES (?)");
$stmt->bind_param("s", $name);

if ($stmt->execute()) {
    respondOK(["category_id" => $stmt->insert_id, "name" => $name], "Category created successfully.");
} else {
    respondInternalError("Failed to create category.");
}

?>

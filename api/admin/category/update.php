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

if (!isset($_POST['category_id']) || !isset($_POST['name'])) {
    respondBadRequest("Category ID and name are required.");
    exit;
}

$category_id = cleanme($_POST['category_id']);
$name = cleanme($_POST['name']);

if (input_is_invalid($category_id) || !is_numeric($category_id)) {
    respondBadRequest("A valid category ID is required.");
    exit;
}

if (input_is_invalid($name)) {
    respondBadRequest("Category name cannot be empty.");
    exit;
}

if (strlen($name) < 2 || strlen($name) > 100) {
    respondBadRequest("Category name must be between 2 and 100 characters.");
    exit;
}

$category_id = (int)$category_id;

// Check category exists
$check = $connect->prepare("SELECT id FROM categories WHERE id = ?");
$check->bind_param("i", $category_id);
$check->execute();

if ($check->get_result()->num_rows == 0) {
    respondBadRequest("Category not found.");
    exit;
}

// Check for duplicate name (excluding current category)
$dupCheck = $connect->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
$dupCheck->bind_param("si", $name, $category_id);
$dupCheck->execute();

if ($dupCheck->get_result()->num_rows > 0) {
    respondBadRequest("Another category with this name already exists.");
    exit;
}

$stmt = $connect->prepare("UPDATE categories SET name = ? WHERE id = ?");
$stmt->bind_param("si", $name, $category_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    respondOK(["category_id" => $category_id, "name" => $name], "Category updated successfully.");
} else {
    respondBadRequest("No changes made or update failed.");
}

?>

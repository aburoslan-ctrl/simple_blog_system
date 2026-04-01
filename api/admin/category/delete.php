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

if (!isset($_POST['category_id'])) {
    respondBadRequest("Category ID is required.");
    exit;
}

$category_id = cleanme($_POST['category_id']);

if (input_is_invalid($category_id) || !is_numeric($category_id)) {
    respondBadRequest("A valid category ID is required.");
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

// Remove from post_categories first
$delLinks = $connect->prepare("DELETE FROM post_categories WHERE category_id = ?");
$delLinks->bind_param("i", $category_id);
$delLinks->execute();

// Delete category
$stmt = $connect->prepare("DELETE FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    respondOK([], "Category deleted successfully.");
} else {
    respondBadRequest("Failed to delete category.");
}

?>

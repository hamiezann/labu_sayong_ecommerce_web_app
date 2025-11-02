<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_clean(); // clear any accidental output
require_once '../includes/config.php';


header('Content-Type: application/json'); // <--- Important

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit;
}

// Check if already in wishlist
$check = mysqli_query($conn, "SELECT * FROM wishlist WHERE user_id='$user_id' AND product_id='$product_id'");
if (!$check) {
    echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    exit;
}

if (mysqli_num_rows($check) > 0) {
    mysqli_query($conn, "DELETE FROM wishlist WHERE user_id='$user_id' AND product_id='$product_id'");
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    mysqli_query($conn, "INSERT INTO wishlist (user_id, product_id) VALUES ('$user_id', '$product_id')");
    echo json_encode(['success' => true, 'action' => 'added']);
}

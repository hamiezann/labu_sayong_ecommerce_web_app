<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

$order_id = intval($_POST['order_id']);
$shipping_address = mysqli_real_escape_string($conn, $_POST['shipping_address']);

$proof_path = null;

// Handle PDF upload
if (!empty($_FILES['proof_of_payment']['name'])) {

    $file = $_FILES['proof_of_payment'];

    // Validate file type
    if ($file['type'] !== 'application/pdf') {
        die("<script>alert('Only PDF files are allowed'); history.back();</script>");
    }

    // Create directory if not exist
    $uploadDir = "../uploads/proof_of_payment/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Rename file
    $filename = "proof_" . $order_id . "_" . time() . ".pdf";
    $filePath = $uploadDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        die("<script>alert('Failed to upload file'); history.back();</script>");
    }

    // Store DB path (relative)
    $proof_path = "uploads/proof_of_payment/" . $filename;
}

// Build update query
$updateSql = "UPDATE orders SET shipping_address = '$shipping_address'";

if ($proof_path !== null) {
    $updateSql .= ", proof_of_payment = '$proof_path'";
}

$updateSql .= " WHERE order_id = '$order_id'";

// Run update
if (mysqli_query($conn, $updateSql)) {
    echo "<script>
        alert('Order updated successfully!');
        window.location.href = '../view/customer/order-details.php?id=$order_id';
    </script>";
} else {
    echo "<script>
        alert('Update failed: " . mysqli_error($conn) . "');
        history.back();
    </script>";
}

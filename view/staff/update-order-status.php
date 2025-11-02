<?php
include '../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $staff_id = $_SESSION['user_id']; // staff updating the order

    $query = "UPDATE orders SET status = ?, staff_id = ? WHERE order_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sii", $status, $staff_id, $order_id);
    $stmt->execute();

    header("Location: order-list.php");
    exit;
}

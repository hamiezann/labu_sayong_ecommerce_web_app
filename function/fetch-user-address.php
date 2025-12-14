<?php
session_start();
include '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$userAddress = '';

$userStmt = $conn->prepare("SELECT address FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userStmt->bind_result($userAddress);
$userStmt->fetch();
$userStmt->close();

echo json_encode([
    'address' => $address ?? ''
]);

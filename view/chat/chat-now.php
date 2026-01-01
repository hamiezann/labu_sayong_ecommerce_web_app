<?php
require_once '../../includes/config.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$product_id  = intval($_GET['product_id'] ?? 0);

if (is_staff($customer_id)) {
    echo "<div class='container py-5 text-center'><h4>⚠️ Staff cannot start customer chats.</h4></div>";
    exit();
}

if ($product_id <= 0) {
    echo "<div class='container py-5 text-center'><h4>❌ Invalid product.</h4></div>";
    exit();
}

// 🔍 Check if session already exists
$check = mysqli_query($conn, "
    SELECT session_id 
    FROM chat_sessions 
    WHERE product_id = '$product_id' 
      AND customer_id = '$customer_id'
    LIMIT 1
");

if (mysqli_num_rows($check) > 0) {
    $session = mysqli_fetch_assoc($check);
    header("Location: " . base_url('view/chat/chat.php?session_id=' . $session['session_id']));
    exit();
}

// ❗ NO SESSION YET → go to chat page WITHOUT creating session
header("Location: " . base_url('view/chat/chat.php?product_id=' . $product_id));
exit();

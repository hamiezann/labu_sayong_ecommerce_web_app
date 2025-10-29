<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/auth/login.php");
    exit();
}
// If cart not initialized, create it
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    // If already in cart, increase quantity
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] += 1;
    } else {
        $_SESSION['cart'][$id] = [
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => $quantity
        ];
    }

    $_SESSION['success_message'] = "✅ Product added to cart successfully!";
    header("Location: ../view/customer/cart.php");
    exit();
}

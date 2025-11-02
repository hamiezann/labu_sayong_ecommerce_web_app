<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/auth/login.php");
    exit();
}

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    // --- Variant data ---
    $variant_id = isset($_POST['variant_id']) ? $_POST['variant_id'] : null;
    $color = $_POST['color'] ?? null;
    $size = $_POST['size'] ?? null;
    $pattern = $_POST['pattern'] ?? null;

    // Unique cart key — includes variant
    $cart_key = $product_id;
    if ($variant_id) {
        $cart_key .= "_v{$variant_id}";
    } else {
        // fallback — combine options into unique string
        $cart_key .= "_" . md5(json_encode([$color, $size, $pattern]));
    }

    // --- Add or update cart item ---
    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$cart_key] = [
            'product_id' => $product_id,
            'variant_id' => $variant_id,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => $quantity,
            'options' => [
                'color' => $color,
                'size' => $size,
                'pattern' => $pattern
            ]
        ];
    }



    $_SESSION['success_message'] = "✅ Product added to cart successfully!";
    header("Location: ../view/customer/cart.php");
    exit();
}

<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../view/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    // Variant options
    $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : null;
    $color = $_POST['color'] ?? null;
    $size = $_POST['size'] ?? null;
    $pattern = $_POST['pattern'] ?? null;

    // Get product price and image from DB (snapshot)
    $productQuery = $conn->prepare("SELECT price, image FROM products WHERE product_id = ?");
    $productQuery->bind_param("i", $product_id);
    $productQuery->execute();
    $productQuery->bind_result($price, $image);
    if (!$productQuery->fetch()) {
        $_SESSION['error_message'] = "Product not found.";
        header("Location: ../view/customer/cart.php");
        exit();
    }
    $productQuery->close();

    // --- STEP 1: Get or create active cart ---
    $cartQuery = $conn->prepare("SELECT cart_id FROM carts WHERE user_id = ? LIMIT 1");
    $cartQuery->bind_param("i", $user_id);
    $cartQuery->execute();
    $cartQuery->bind_result($cart_id);
    if (!$cartQuery->fetch()) {
        // No cart exists → create new
        $cartQuery->close();
        $insertCart = $conn->prepare("INSERT INTO carts (user_id) VALUES (?)");
        $insertCart->bind_param("i", $user_id);
        $insertCart->execute();
        $cart_id = $insertCart->insert_id;
        $insertCart->close();
    } else {
        $cartQuery->close();
    }

    // --- STEP 2: Check if item already exists in cart ---
    $checkItem = $conn->prepare("
        SELECT cart_item_id, quantity 
        FROM cart_items 
        WHERE cart_id = ? 
          AND product_id = ? 
          AND IFNULL(variant_id, 0) = IFNULL(?, 0)
          AND IFNULL(color, '') = IFNULL(?, '')
          AND IFNULL(size, '') = IFNULL(?, '')
          AND IFNULL(pattern, '') = IFNULL(?, '')
        LIMIT 1
    ");
    $checkItem->bind_param("iiisss", $cart_id, $product_id, $variant_id, $color, $size, $pattern);
    $checkItem->execute();
    $checkItem->bind_result($cart_item_id, $existing_qty);

    if ($checkItem->fetch()) {
        // Item exists → update quantity
        $checkItem->close();
        $updateItem = $conn->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE cart_item_id = ?");
        $updateItem->bind_param("ii", $quantity, $cart_item_id);
        $updateItem->execute();
        $updateItem->close();
    } else {
        // Item does not exist → insert new
        $checkItem->close();
        $insertItem = $conn->prepare("
            INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, price, color, size, pattern)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insertItem->bind_param("iiiidsss", $cart_id, $product_id, $variant_id, $quantity, $price, $color, $size, $pattern);
        $insertItem->execute();
        $insertItem->close();
    }

    $_SESSION['success_message'] = "✅ Product added to cart successfully!";
    header("Location: ../view/customer/cart.php");
    exit();
}

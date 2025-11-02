<?php
session_start();

include '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    $_SESSION['error_message'] = "Your cart is empty.";
    header("Location: cart.php");
    exit();
}

// --- HANDLE CHECKOUT FORM ---
// --- HANDLE CHECKOUT FORM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $shipping_option = $_POST['shipping_option'] ?? 'Peninsular';
    $payment_method = $_POST['payment_method'] ?? 'Cash on Delivery';
    $notes = trim($_POST['notes'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // 🟩 Calculate subtotal, total quantity
    $subtotal = 0;
    $total_quantity = 0;
    foreach ($cart as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $total_quantity += $item['quantity'];
    }

    // 🟩 Apply 10% discount if total quantity > 100
    $discount = 0;
    if ($total_quantity > 100) {
        $discount = $subtotal * 0.10;
        $subtotal -= $discount;
        $notes .= "\n📌 Discount applied: 10% off for bulk purchase (Qty over 100).";
    }

    // 🟩 Calculate shipping fee
    $shipping_fee = 0;
    switch ($shipping_option) {
        case 'Peninsular':
            $shipping_fee = 20;
            break;
        case 'Sabah/Sarawak':
            $shipping_fee = 30;
            break;
        case 'Overseas':
            $shipping_fee = 0;
            $notes .= "\n✈️ Shipping note: Overseas orders require manual confirmation.";
            break;
    }

    // 🟩 Final total
    $total_price = $subtotal + $shipping_fee;

    // --- INSERT INTO orders ---
    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, subtotal, shipping_fee, total_price, payment_method, shipping_address, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("idddsss", $user_id, $subtotal, $shipping_fee, $total_price, $payment_method, $address, $notes);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // --- INSERT order_items ---
    $item_stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, variant_id, quantity, price, color, size, pattern)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($cart as $item) {
        $variant_id = $item['variant_id'] ?? null;
        $color = $item['options']['color'] ?? null;
        $size = $item['options']['size'] ?? null;
        $pattern = $item['options']['pattern'] ?? null;

        $item_stmt->bind_param(
            "iiiidsss",
            $order_id,
            $item['product_id'],
            $variant_id,
            $item['quantity'],
            $item['price'],
            $color,
            $size,
            $pattern
        );
        $item_stmt->execute();
    }
    $item_stmt->close();

    // --- Clear cart ---
    unset($_SESSION['cart']);

    $_SESSION['success_message'] = "✅ Order placed successfully! Discount applied if eligible.";
    header("Location: success-order.php?order_id=$order_id");
    exit();
}

include '../customer/header.php';
// echo json_encode($cart);
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4 text-primary"><i class="bi bi-credit-card me-2"></i>Checkout</h2>

    <div class="row">
        <!-- LEFT: SHIPPING & PAYMENT -->
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Shipping Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Shipping Address</label>
                            <textarea name="address" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Shipping Option</label>
                            <select name="shipping_option" class="form-select" required>
                                <option value="Peninsular">Peninsular Malaysia (RM 20)</option>
                                <option value="Sabah/Sarawak">Sabah / Sarawak (RM 30)</option>
                                <option value="Overseas">Overseas (RM 0)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="Cash on Delivery">Cash on Delivery</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="FPX">FPX Online Payment</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Order Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional instructions or comments..."></textarea>
                        </div>

                        <button type="submit" name="place_order" class="btn btn-success w-100 py-2">
                            <i class="bi bi-check-circle me-2"></i>Place Order
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- RIGHT: ORDER SUMMARY -->
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                </div>
                <div class="card-body">
                    <?php $subtotal = 0; ?>
                    <?php foreach ($cart as $item): ?>
                        <?php $subtotal += $item['price'] * $item['quantity']; ?>
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?= base_url($item['image']) ?>" width="60" height="60" class="rounded me-3" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                <small class="text-muted">
                                    Qty: <?= $item['quantity'] ?>
                                    <?= $item['options']['color'] ? "| Color: {$item['options']['color']}" : "" ?>
                                    <?= $item['options']['size'] ? "| Size: {$item['options']['size']}" : "" ?>
                                    <?= $item['options']['pattern'] ? "| Pattern: {$item['options']['pattern']}" : "" ?>
                                </small>
                            </div>
                            <span class="fw-semibold">RM <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                        <hr>
                    <?php endforeach; ?>

                    <?php
                    $total_quantity = array_sum(array_column($cart, 'quantity'));
                    $discount = ($total_quantity > 100) ? ($subtotal * 0.10) : 0;
                    ?>
                    <div class="d-flex justify-content-between">
                        <span>Subtotal</span>
                        <strong>RM <?= number_format($subtotal, 2) ?></strong>
                    </div>
                    <?php if ($discount > 0): ?>
                        <div class="d-flex justify-content-between text-success">
                            <span>Bulk Discount (10%)</span>
                            <strong>- RM <?= number_format($discount, 2) ?></strong>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between">
                        <span>Shipping (calculated at next step)</span>
                        <strong>-</strong>
                    </div>


                    <hr>
                    <div class="text-end">
                        <em class="text-muted small">Total will be updated after selecting shipping option.</em>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php include '../customer/footer.php'; ?>
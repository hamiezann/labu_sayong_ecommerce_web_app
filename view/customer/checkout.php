<?php
ob_start();
include '../customer/header.php';
include '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$cartQuery = $conn->prepare("SELECT cart_id FROM carts WHERE user_id = ? LIMIT 1");
$cartQuery->bind_param("i", $user_id);
$cartQuery->execute();
$cartQuery->bind_result($cart_id);
$hasCart = $cartQuery->fetch();
$cartQuery->close();

if (!$hasCart) {
    $_SESSION['error_message'] = "Your cart is empty.";
    header("Location: cart.php");
    exit();
}

$itemsQuery = $conn->prepare("
    SELECT ci.cart_item_id, ci.product_id, ci.variant_id, ci.quantity, ci.price, ci.color, ci.size, ci.pattern, p.name, p.image, p.stock
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.product_id
    WHERE ci.cart_id = ?
");
$itemsQuery->bind_param("i", $cart_id);
$itemsQuery->execute();
$result = $itemsQuery->get_result();
$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[$row['cart_item_id']] = $row;
}
$itemsQuery->close();

if (empty($cartItems)) {
    $_SESSION['error_message'] = "Your cart is empty.";
    header("Location: cart.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {

    $shipping_option = $_POST['shipping_option'] ?? 'Peninsular';
    $payment_method = $_POST['payment_method'] ?? 'Cash on Delivery';
    $notes = trim($_POST['notes'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $proof_of_payment = null;

    if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/proof_of_payments/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $file_tmp = $_FILES['proof_of_payment']['tmp_name'];
        $file_name = uniqid('proof_') . '_' . basename($_FILES['proof_of_payment']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $file_path)) {
            $proof_of_payment = 'uploads/proof_of_payments/' . $file_name;
        }
    }

    $subtotal = 0;
    $total_quantity = 0;
    foreach ($cartItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
        $total_quantity += $item['quantity'];
    }

    $discount = 0;
    if ($total_quantity > 100) {
        $discount = $subtotal * 0.10;
        $subtotal -= $discount;
        $notes .= "\n📌 Discount applied: 10% off for bulk purchase (Qty over 100).";
    }

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

    $total_price = $subtotal + $shipping_fee;

    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, subtotal, shipping_fee, total_price, payment_method, shipping_address, notes, proof_of_payment)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("idddssss", $user_id, $subtotal, $shipping_fee, $total_price, $payment_method, $address, $notes, $proof_of_payment);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    $item_stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, variant_id, quantity, price, color, size, pattern)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($cartItems as $item) {
        $variant_id = $item['variant_id'] ?? null;
        $color = $item['color'] ?? null;
        $size = $item['size'] ?? null;
        $pattern = $item['pattern'] ?? null;

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
        if ($variant_id) {

            $updateVariant = $conn->prepare("
        UPDATE product_variants 
        SET stock = stock - ? 
        WHERE variant_id = ?
    ");
            $updateVariant->bind_param("ii", $item['quantity'], $variant_id);
            $updateVariant->execute();
            $updateVariant->close();
        } else {
            // Deduct from main product stock
            $updateProduct = $conn->prepare("
        UPDATE products
        SET stock = stock - ?
        WHERE product_id = ?
    ");
            $updateProduct->bind_param("ii", $item['quantity'], $item['product_id']);
            $updateProduct->execute();
            $updateProduct->close();
        }
    }
    $item_stmt->close();
    $deleteCartItems = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $deleteCartItems->bind_param("i", $cart_id);
    $deleteCartItems->execute();
    $deleteCartItems->close();
    $deleteCart = $conn->prepare("DELETE FROM carts WHERE cart_id = ?");
    $deleteCart->bind_param("i", $cart_id);
    $deleteCart->execute();
    $deleteCart->close();

    $_SESSION['success_message'] = "✅ Order placed successfully! Discount applied if eligible.";
    header("Location: success-order.php?order_id=$order_id");
    exit();
}
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4 text-primary"><i class="bi bi-credit-card me-2"></i>Checkout</h2>

    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header back-info-custom text-white d-flex align-items-center">
                    <i class="bi bi-truck me-2"></i>
                    <h5 class="mb-0">Shipping & Payment</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Shipping Address</label>
                            <textarea name="address" class="form-control" rows="3" placeholder="Enter your full address..." required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Shipping Option</label>
                            <select name="shipping_option" class="form-select" required>
                                <option value="Peninsular">Peninsular Malaysia (RM 20)</option>
                                <option value="Sabah/Sarawak">Sabah / Sarawak (RM 30)</option>
                                <option value="Overseas">Overseas (RM 0)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="bi bi-qr-code fs-4 me-3"></i>
                                <div>
                                    <strong>QR Transfer</strong> is the default and only available payment method.
                                </div>
                            </div>
                            <input type="hidden" name="payment_method" value="QR Transfer">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Order Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional instructions or comments..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Proof of Payment</label>
                            <input type="file" name="proof_of_payment" accept=".pdf" class="form-control">
                        </div>

                        <button type="submit" name="place_order" class="btn btn-success w-100 py-2">
                            <i class="bi bi-check-circle me-2"></i>Place Order
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- RIGHT: QR & ORDER SUMMARY -->
        <div class="col-md-5">
            <div class="card shadow-sm mb-4 text-center">
                <div class="card-header back-success-custom text-white">
                    <h5 class="mb-0"><i class="bi bi-qr-code me-2"></i>QR Payment</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Scan the QR code below to make payment:</p> <img src="<?= base_url('assets/img/qr-payment.jpg') ?>" alt="QR Code" class="img-fluid rounded shadow-sm mb-3" style="max-width: 250px;">
                    <p class="fw-semibold mb-0">Account Name: Labu Sayong Ceramics</p>
                    <p class="text-muted small mb-0">Bank: Maybank | Acc No: 1234567890</p>
                    <p class="text-success small mt-2"><i class="bi bi-shield-check"></i> Secure QR Transfer Enabled</p>
                </div>
            </div>

            <!-- RIGHT: ORDER SUMMARY -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Order Summary</h5>
                </div>
                <div class="card-body">
                    <?php $subtotal = 0; ?>
                    <?php foreach ($cartItems as $item): ?>
                        <?php $subtotal += $item['price'] * $item['quantity']; ?>
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?= base_url($item['image']) ?>" width="60" height="60" class="rounded me-3" style="object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                <small class="text-muted">
                                    Qty: <?= $item['quantity'] ?>
                                    <?= $item['color'] ? "| Color: {$item['color']}" : "" ?>
                                    <?= $item['size'] ? "| Size: {$item['size']}" : "" ?>
                                    <?= $item['pattern'] ? "| Pattern: {$item['pattern']}" : "" ?>
                                </small>
                            </div>
                            <span class="fw-semibold">RM <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                        <hr>
                    <?php endforeach; ?>

                    <?php
                    $total_quantity = array_sum(array_column($cartItems, 'quantity'));
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
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 12px;
        overflow: hidden;
    }

    .btn-success {
        background: linear-gradient(135deg, #00c49a, #00a37a);
        border: none;
        transition: all 0.3s ease;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        opacity: 0.9;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../customer/footer.php'; ?>
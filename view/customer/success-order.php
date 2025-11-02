<?php

include '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    header("Location: cart.php");
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];

// Fetch order details
$orderQuery = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$orderQuery->bind_param("ii", $order_id, $user_id);
$orderQuery->execute();
$order = $orderQuery->get_result()->fetch_assoc();
$orderQuery->close();

if (!$order) {
    echo "<div class='container py-5 text-center'><h4>❌ Order not found.</h4></div>";
    include '../customer/footer.php';
    exit();
}

// Fetch order items
$itemQuery = $conn->prepare("
    SELECT oi.*, p.name, p.image 
    FROM order_items oi 
    INNER JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$itemQuery->bind_param("i", $order_id);
$itemQuery->execute();
$items = $itemQuery->get_result();

include '../customer/header.php';
?>

<div class="container py-5">
    <div class="card shadow-lg border-0">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
            <h2 class="fw-bold mt-3">Thank You for Your Order!</h2>
            <p class="text-muted mb-4">Your order has been placed successfully. A confirmation has been recorded in your account.</p>

            <div class="card mx-auto border-0 shadow-sm" style="max-width: 700px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Order ID:</strong></span>
                        <span>#<?= $order['order_id'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Date:</strong></span>
                        <span><?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Status:</strong></span>
                        <span class="badge bg-success"><?= $order['status'] ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Payment Method:</strong></span>
                        <span><?= htmlspecialchars($order['payment_method']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span><strong>Shipping Address:</strong></span>
                        <span class="text-end"><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></span>
                    </div>
                    <div class="mt-3">
                        <strong>Notes:</strong><br>
                        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($order['notes'])) ?: 'No additional notes.' ?></p>
                    </div>
                </div>
            </div>

            <div class="card mt-4 mx-auto border-0 shadow-sm" style="max-width: 700px;">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Ordered Items</h5>
                </div>
                <div class="card-body">
                    <?php $subtotal = 0; ?>
                    <?php while ($item = $items->fetch_assoc()): ?>
                        <?php $subtotal += $item['price'] * $item['quantity']; ?>
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?= base_url($item['image']) ?>" width="70" height="70" class="rounded me-3" style="object-fit: cover;">
                            <div class="flex-grow-1 text-start">
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
                    <?php endwhile; ?>

                    <div class="d-flex justify-content-between">
                        <span>Subtotal</span>
                        <strong>RM <?= number_format($subtotal, 2) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Shipping Fee</span>
                        <strong>RM <?= number_format($order['shipping_fee'], 2) ?></strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5">
                        <strong>Total</strong>
                        <strong class="text-success">RM <?= number_format($order['total_price'], 2) ?></strong>
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <a href="../shop-listing.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-bag"></i> Continue Shopping
                </a>
                <a href="my-orders.php" class="btn btn-success">
                    <i class="bi bi-list-check"></i> View My Orders
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
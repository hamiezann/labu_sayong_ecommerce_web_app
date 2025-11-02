<?php
session_start();
include '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Validate order_id
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    header("Location: my-orders.php");
    exit();
}

$order_id = intval($_GET['order_id']);

// --- Fetch Order Info ---
$orderQuery = $conn->prepare("
    SELECT o.*, u.FullName
    FROM orders o
    INNER JOIN users u ON o.user_id = u.id
    WHERE o.order_id = ? AND o.user_id = ?
");
$orderQuery->bind_param("ii", $order_id, $user_id);
$orderQuery->execute();
$order = $orderQuery->get_result()->fetch_assoc();
$orderQuery->close();

if (!$order) {
    $_SESSION['error_message'] = "Order not found.";
    header("Location: my-orders.php");
    exit();
}

// --- Fetch Order Items ---
$itemQuery = $conn->prepare("
    SELECT oi.*, p.name, p.image
    FROM order_items oi
    INNER JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$itemQuery->bind_param("i", $order_id);
$itemQuery->execute();
$items = $itemQuery->get_result();
$itemQuery->close();

include '../customer/header.php';
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary"><i class="bi bi-receipt-cutoff me-2"></i>Order Details</h3>
        <a href="my-orders.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Orders</a>
    </div>

    <!-- Order Summary -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Order Summary</h5>
            <span class="badge bg-light text-dark px-3 py-2"><?= htmlspecialchars($order['status']) ?></span>
        </div>
        <div class="card-body">
            <div class="row gy-3">
                <div class="col-md-6">
                    <p><strong>Order ID:</strong> #<?= $order['order_id'] ?></p>
                    <p><strong>Order Date:</strong> <?= date("d M Y, h:i A", strtotime($order['order_date'])) ?></p>
                    <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Shipping Address:</strong><br><?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
                    <p><strong>Notes:</strong><br><?= nl2br(htmlspecialchars($order['notes'])) ?: '-' ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-bag-check me-2"></i>Ordered Items</h5>
        </div>
        <div class="card-body">
            <?php if ($items->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Details</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price (RM)</th>
                                <th class="text-end">Total (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $subtotal = 0;
                            while ($item = $items->fetch_assoc()):
                                $total_item_price = $item['quantity'] * $item['price'];
                                $subtotal += $total_item_price;
                            ?>
                                <tr>
                                    <td width="120">
                                        <img src="<?= base_url($item['image']) ?>" class="img-fluid rounded shadow-sm" style="max-height: 80px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                        <small class="text-muted">
                                            <?= $item['color'] ? "Color: " . htmlspecialchars($item['color']) . " | " : "" ?>
                                            <?= $item['size'] ? "Size: " . htmlspecialchars($item['size']) . " | " : "" ?>
                                            <?= $item['pattern'] ? "Pattern: " . htmlspecialchars($item['pattern']) : "" ?>
                                        </small>
                                    </td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end"><?= number_format($item['price'], 2) ?></td>
                                    <td class="text-end"><?= number_format($total_item_price, 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-3">No items found for this order.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="card shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Payment Summary</h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-end">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td>Subtotal</td>
                            <td class="text-end">RM <?= number_format($order['subtotal'] ?? $subtotal, 2) ?></td>
                        </tr>
                        <?php if (strpos($order['notes'], 'Discount applied') !== false): ?>
                            <tr class="text-success">
                                <td>Bulk Discount (10%)</td>
                                <td class="text-end">- RM <?= number_format(($order['subtotal'] * 0.10), 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td>Shipping Fee</td>
                            <td class="text-end">RM <?= number_format($order['shipping_fee'], 2) ?></td>
                        </tr>
                        <tr class="border-top fw-bold">
                            <td>Total Paid</td>
                            <td class="text-end text-success fs-5">RM <?= number_format($order['total_price'], 2) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../customer/footer.php'; ?>
<?php
include '../../includes/config.php';

if (!isset($_GET['order_id'])) {
    echo "<div class='alert alert-danger'>Invalid order ID.</div>";
    exit();
}

$order_id = intval($_GET['order_id']);

// 🟩 Fetch main order info
$query = "SELECT o.*, u.FullName AS customer_name, s.FullName AS staff_name
          FROM orders o
          INNER JOIN users u ON o.user_id = u.id
          LEFT JOIN users s ON o.staff_id = s.id
          WHERE o.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "<div class='alert alert-warning'>Order not found.</div>";
    exit();
}

// 🟩 Fetch order items (with product image)
$itemQuery = "SELECT oi.*, p.name, p.image
              FROM order_items oi
              INNER JOIN products p ON oi.product_id = p.product_id
              WHERE oi.order_id = ?";
$itemStmt = $conn->prepare($itemQuery);
$itemStmt->bind_param("i", $order_id);
$itemStmt->execute();
$items = $itemStmt->get_result();
?>
<style>
    .modal-content {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        background: linear-gradient(90deg, #198754, #28a745);
        color: white;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }
</style>
<div class="container-fluid text-start">
    <div class="row mb-4">
        <div class="col-md-6">
            <h6><i class="bi bi-person-circle"></i> Customer</h6>
            <p><?= htmlspecialchars($order['customer_name']) ?></p>

            <h6><i class="bi bi-geo-alt"></i> Shipping Address</h6>
            <p><?= nl2br(htmlspecialchars($order['shipping_address'] ?? 'N/A')) ?></p>
        </div>

        <div class="col-md-6">
            <h6><i class="bi bi-credit-card"></i> Payment Method</h6>
            <p><?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></p>

            <h6><i class="bi bi-document-text"></i> Proof of Payment</h6>
            <?php if ($order['proof_of_payment'] && file_exists('../../' . $order['proof_of_payment'])): ?>
                <embed src="<?= base_url($order['proof_of_payment']) ?>" type="application/pdf" width="100%" height="400px">
            <?php else: ?>
                <p class="text-muted">No proof of payment uploaded.</p>
            <?php endif; ?>


            <h6><i class="bi bi-person-gear"></i> Staff In Charge</h6>
            <p><?= htmlspecialchars($order['staff_name'] ?? '-') ?></p>

            <h6><i class="bi bi-chat-left-text"></i> Notes</h6>
            <p><?= nl2br(htmlspecialchars($order['notes'] ?? '-')) ?></p>
        </div>
    </div>

    <hr>

    <h5 class="mt-3 mb-3"><i class="bi bi-box-seam"></i> Ordered Items</h5>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price (RM)</th>
                    <th>Options</th>
                    <th>Total (RM)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grandTotal = 0;
                while ($row = $items->fetch_assoc()):
                    $itemTotal = $row['quantity'] * $row['price'];
                    $grandTotal += $itemTotal;
                ?>
                    <tr>
                        <td>
                            <img src="<?= base_url($row['image']) ?>" alt="Product Image"
                                style="width: 70px; height: 70px; object-fit: cover;" class="rounded shadow-sm">
                        </td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= number_format($row['price'], 2) ?></td>
                        <td>
                            <?php if ($row['color']): ?>
                                <div><strong>Color:</strong> <span style="color: <?= htmlspecialchars($row['color']) ?>;">■</span> <?= htmlspecialchars($row['color']) ?></div>
                            <?php endif; ?>
                            <?php if ($row['size']): ?>
                                <div><strong>Size:</strong> <?= htmlspecialchars($row['size']) ?></div>
                            <?php endif; ?>
                            <?php if ($row['pattern']): ?>
                                <div><strong>Design:</strong> <?= htmlspecialchars($row['pattern']) ?></div>
                            <?php endif; ?>
                            <?php if (!$row['color'] && !$row['size'] && !$row['pattern']): ?>
                                <span class="text-muted">No options</span>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($itemTotal, 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Grand Total:</th>
                    <th class="text-success">RM <?= number_format($grandTotal, 2) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
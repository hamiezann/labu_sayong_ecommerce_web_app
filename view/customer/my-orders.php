<?php
require_once '../../includes/config.php'; // adjust path if needed

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all user orders
$query = "
    SELECT *
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.order_date DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include '../customer/header.php';
?>

<style>
    /* Custom table styling */
    .table-custom {
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .table-custom thead th {
        background: #f8f9fa;
        font-weight: 600;
        padding: 15px;
        border-bottom: 2px solid #e9ecef;
    }

    .table-custom tbody tr {
        transition: background 0.2s ease;
    }

    .table-custom tbody tr:hover {
        background: #f1f5f9;
    }

    .table-custom td {
        padding: 14px;
        vertical-align: middle;
    }

    /* Rounded badge style */
    .badge {
        padding: 7px 12px;
        font-size: 0.85rem;
        border-radius: 30px;
        color: white !important;
    }

    .back-success-custom {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .back-warning-custom {
        background: linear-gradient(135deg, #facc15 0%, #e5eb7d 100%);
        color: #333 !important;
    }

    .back-danger-custom {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .back-info-custom {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    /* Action button styling */
    .btn-view-details {
        border-radius: 25px;
        padding: 6px 14px;
        font-size: 0.85rem;
    }
</style>

<!-- <body class="bg-light"> -->
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded-3 shadow-sm"
        style="background:#fff;">
        <h3 class="fw-bold mb-0 d-flex align-items-center" style="color:#222;">
            <i class="bi bi-chat-dots-fill me-2 fs-4"></i>
            My Orders
        </h3>
    </div>


    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <!-- <table class="table table-bordered align-middle"> -->
            <table class="table table-bordered align-middle table-custom">
                <thead class="table-light">
                    <tr>
                        <th>#Order ID</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Shipping</th>
                        <th>Total (RM)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['order_id'] ?></td>
                            <td><?= date('d M Y, h:i A', strtotime($row['order_date'])) ?></td>
                            <td>
                                <?php
                                $statusClass = [
                                    'Pending' => 'back-warning-custom',
                                    'Processing' => 'back-info-custom',
                                    'Completed' => 'back-success-custom',
                                    'Cancelled' => 'back-danger-custom'
                                ][$row['status']] ?? 'secondary';
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= $row['status'] ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['shipping_fee'] ?? '-') ?></td>
                            <td><?= number_format($row['total_price'], 2) ?></td>
                            <td>
                                <a href="order-details.php?order_id=<?= $row['order_id'] ?>" class="btn btn-view">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You have not placed any orders yet.</div>
    <?php endif; ?>
</div>
<!-- </body> -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<?php include '../customer/footer.php'; ?>
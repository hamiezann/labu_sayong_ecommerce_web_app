<?php
include '../../includes/config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$query = "SELECT o.*, u.FullName, s.FullName AS staff_name 
          FROM orders o
          INNER JOIN users u ON o.user_id = u.id
          LEFT JOIN users s ON o.staff_id = s.id
          ORDER BY o.status";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

include '../../template/header.php';
include '../../template/sidebar.php';

$page = 'order';
$subPage = 'view-orders';
$pageName = 'Order Management';
?>

<main class="app-main">
    <div class="app-content p-4">
        <div class="container-fluid">
            <h1 class="mb-4 d-flex align-items-center">
                <i class="bi bi-cart-check me-2"></i><?= $pageName ?>
            </h1>

            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>All Orders</h5>
                </div>

                <div class="card-body">
                    <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table id="orderTable" class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Customer</th>
                                        <th>Total (RM)</th>
                                        <th>Status</th>
                                        <th>Staff</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['order_id'] ?></td>
                                            <td><?= htmlspecialchars($row['FullName']) ?></td>
                                            <td><?= number_format($row['total_price'], 2) ?></td>
                                            <td>
                                                <form method="POST" action="update-order-status.php" class="d-flex align-items-center">
                                                    <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
                                                    <select name="status" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                                                        <?php
                                                        $statuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
                                                        foreach ($statuses as $status) {
                                                            $selected = ($status == $row['status']) ? 'selected' : '';
                                                            echo "<option value='$status' $selected>$status</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?= htmlspecialchars($row['staff_name'] ?? '-') ?></td>
                                            <td><?= date('d M Y', strtotime($row['order_date'])) ?></td>
                                            <td>
                                                <button class="btn btn-outline-primary btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#orderDetailModal"
                                                    data-orderid="<?= $row['order_id'] ?>">
                                                    <i class="bi bi-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">No orders available.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="orderDetailLabel"><i class="bi bi-receipt"></i> Order Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div id="orderDetailsContent" class="p-2 text-center text-muted">
                    Loading details...
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include '../../template/footer.php'; ?>

<script>
    // Initialize DataTable
    new DataTable('#orderTable');

    // Load order details dynamically
    const orderDetailModal = document.getElementById('orderDetailModal');
    orderDetailModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const orderId = button.getAttribute('data-orderid');
        const contentDiv = document.getElementById('orderDetailsContent');

        fetch(`get-order-details.php?order_id=${orderId}`)
            .then(response => response.text())
            .then(data => {
                contentDiv.innerHTML = data;
            })
            .catch(err => {
                contentDiv.innerHTML = `<div class='text-danger'>Error loading details</div>`;
            });
    });
</script>
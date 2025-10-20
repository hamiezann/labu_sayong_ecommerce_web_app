<?php
include '../../includes/config.php';
include '../../template/header.php';
include '../../template/sidebar.php';

$page = 'order';
$subPage = 'view-orders';
$pageName = 'Order Management';
?>

<main class="app-main">
    <div class="app-content p-4">
        <div class="container-fluid">
            <h1 class="mb-4"><i class="bi bi-cart-check me-2"></i><?= $pageName ?></h1>

            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Current Orders</h5>
                </div>
                <div class="card-body">
                    <table id="orderTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer Name</th>
                                <th>Product</th>
                                <th>Total (RM)</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td>Customer <?= $i ?></td>
                                    <td>Labu Sayong Pot <?= $i ?></td>
                                    <td><?= 50 * $i ?></td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td><?= date('Y-m-d') ?></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../template/footer.php'; ?>

<script>
    new DataTable('#orderTable');
</script>
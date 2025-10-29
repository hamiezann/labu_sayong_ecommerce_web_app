<?php
include '../../includes/config.php';
// set page variables
$page = 'dashboard';
$pageName = 'Dashboard';

if (!isset($_SESSION['user_id'])) {
    redirect('view/auth/login.php');
    exit();
}
// getRoutePermission($_SERVER['PHP_SELF'], $userInfo['Role']);
include '../../template/header.php';
include '../../template/sidebar.php';




//  call total products, orders, users, sales from database
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM products"))['count'];
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM orders"))['count'];
$totalClient = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM users WHERE Role='customer'"))['count'];
$totalSales = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_price) AS sum FROM orders WHERE status='completed'"))['sum'];
$orderList = mysqli_query($conn, "SELECT * FROM orders  ORDER BY order_id DESC LIMIT 5");
?>

<!-- Main content -->
<main class="app-main">
    <div class="app-content p-4">
        <div class="container-fluid">
            <h1 class="mb-4"><i class="bi bi-speedometer2 me-2"></i><?= $pageName ?></h1>

            <!-- Dashboard Widgets Example -->
            <div class="row g-4">
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-box-seam fs-1 text-primary mb-3"></i>
                            <h5 class="card-title">Total Products</h5>
                            <p class="fs-4 fw-bold"><?= $totalProducts ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-cart4 fs-1 text-success mb-3"></i>
                            <h5 class="card-title">Orders Today</h5>
                            <p class="fs-4 fw-bold"><?= $totalOrders ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-people fs-1 text-warning mb-3"></i>
                            <h5 class="card-title">Active Users</h5>
                            <p class="fs-4 fw-bold"><?= $totalClient ?></p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-12">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <i class="bi bi-cash-stack fs-1 text-danger mb-3"></i>
                            <h5 class="card-title">Total Sales</h5>
                            <p class="fs-4 fw-bold">RM <?= $totalSales ? $totalSales : 0.00 ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Example DataTable -->
            <div class="card mt-5 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Recent Orders</h5>
                </div>
                <div class="card-body">
                    <table id="ordersTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            while ($order = mysqli_fetch_assoc($orderList)):
                            ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td><?= htmlspecialchars($order["user_id"]) ?></td>
                                    <td>Labu Sayong Pot <?= $i ?></td>
                                    <td>RM<?= htmlspecialchars($order["total_price"]) ?></td>
                                    <td><?= htmlspecialchars($order["order_date"] ?? date('Y-m-d')) ?></td>
                                </tr>
                            <?php
                                $i++;
                            endwhile;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>

<?php
// include footer
include '../../template/footer.php';
?>

<!-- Optional JS for DataTables -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        new DataTable('#ordersTable');
    });
</script>
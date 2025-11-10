<?php
include '../../includes/config.php';

// Make sure admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Check role
$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'"));
if ($user['Role'] !== 'admin') {
    die("Access denied.");
}

$page = 'reports';
$pageName = 'Reports & Analytics';

include '../../template/header.php';
include '../../template/sidebar.php';

// Optional: date filter
$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
$end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Queries
$totalSales = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total_price) AS total 
    FROM orders 
    WHERE DATE(order_date) BETWEEN '$start' AND '$end' AND status='Completed'
"))['total'] ?? 0;

$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count 
    FROM orders 
    WHERE DATE(order_date) BETWEEN '$start' AND '$end'
"))['count'] ?? 0;

$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count FROM products
"))['count'] ?? 0;

$totalCustomers = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count FROM users WHERE Role='customer'
"))['count'] ?? 0;

$totalStaff = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count FROM users WHERE Role='staff'
"))['count'] ?? 0;

$topProducts = mysqli_query($conn, "
    SELECT p.name, SUM(oi.quantity) AS qty_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.status='Completed' AND DATE(o.order_date) BETWEEN '$start' AND '$end'
    GROUP BY p.product_id
    ORDER BY qty_sold DESC
    LIMIT 5
");

?>

<main class="app-main">
    <div class="app-content p-4" style="background-color: #f7f8fc; min-height: 100vh;">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold text-dark">
                    <i class="bi bi-graph-up-arrow me-2 text-primary"></i><?= $pageName ?>
                </h1>
            </div>

            <!-- Date Filter -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="start" class="form-label fw-semibold text-secondary">Start Date</label>
                    <input type="date" id="start" name="start" value="<?= $start ?>" class="form-control shadow-sm">
                </div>
                <div class="col-md-3">
                    <label for="end" class="form-label fw-semibold text-secondary">End Date</label>
                    <input type="date" id="end" name="end" value="<?= $end ?>" class="form-control shadow-sm">
                </div>
                <div class="col-md-3 align-self-end">
                    <button class="btn btn-gradient me-2 shadow-sm">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="report-pdf.php?start=<?= $start ?>&end=<?= $end ?>" class="btn btn-outline-danger shadow-sm">
                        <i class="bi bi-file-earmark-pdf"></i> PDF
                    </a>
                </div>
            </form>

            <!-- Summary Cards -->
            <div class="row g-4">
                <?php
                $cards = [
                    ["Total Revenue", "RM " . number_format($totalSales, 2), "bi-cash-stack", "text-success", "linear-gradient(135deg,#00C49A,#009F82)"],
                    ["Total Orders", $totalOrders, "bi-cart4", "text-primary", "linear-gradient(135deg,#6C63FF,#5145CD)"],
                    ["Products", $totalProducts, "bi-box-seam", "text-warning", "linear-gradient(135deg,#FFC107,#FF9800)"],
                    ["Customers", $totalCustomers, "bi-people", "text-danger", "linear-gradient(135deg,#FF6F61,#D9534F)"]
                ];
                $i = 0;
                foreach ($cards as $c):
                ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card metric-card text-center border-0 shadow-sm" style="background: <?= $cards[$i][4] ?>;">
                            <div class="card-body text-white">
                                <i class="bi <?= $cards[$i][2] ?> fs-1 mb-2"></i>
                                <h5><?= $cards[$i][0] ?></h5>
                                <p class="fs-4 fw-bold"><?= $cards[$i][1] ?></p>
                            </div>
                        </div>
                    </div>
                <?php $i++;
                endforeach; ?>
            </div>

            <!-- Top Products -->
            <div class="card mt-5 shadow-sm border-0">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg,#6C63FF,#00C49A);">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Top Selling Products</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($topProducts) > 0): ?>
                        <canvas id="topProductsChart" height="120"></canvas>
                        <div class="table-responsive mt-4">
                            <table class="table table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th>Quantity Sold</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $labels = [];
                                    $data = [];
                                    $i = 1;
                                    while ($p = mysqli_fetch_assoc($topProducts)):
                                        $labels[] = $p['name'];
                                        $data[] = $p['qty_sold'];
                                    ?>
                                        <tr>
                                            <td><?= $i++ ?></td>
                                            <td><?= htmlspecialchars($p['name']) ?></td>
                                            <td><?= htmlspecialchars($p['qty_sold']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No data available for the selected date range.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</main>

<style>
    .btn-gradient {
        background: linear-gradient(135deg, #6C63FF, #00C49A);
        border: none;
        color: white;
        transition: 0.3s ease;
    }

    .btn-gradient:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .metric-card {
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    <?php if (!empty($labels)): ?>
        const ctx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Quantity Sold',
                    data: <?= json_encode($data) ?>,
                    backgroundColor: 'rgba(108,99,255,0.8)',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    <?php endif; ?>
</script>


<?php include '../../template/footer.php'; ?>
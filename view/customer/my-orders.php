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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4">My Orders</h2>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
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
                                        'Pending' => 'warning',
                                        'Processing' => 'info',
                                        'Completed' => 'success',
                                        'Cancelled' => 'danger'
                                    ][$row['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= $row['status'] ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['shipping_fee'] ?? '-') ?></td>
                                <td><?= number_format($row['total_price'], 2) ?></td>
                                <td>
                                    <a href="order-details.php?order_id=<?= $row['order_id'] ?>" class="btn btn-sm btn-outline-primary">View Details</a>
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
</body>

</html>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<?php include '../customer/footer.php'; ?>
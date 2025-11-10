<?php
require '../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

include '../../includes/config.php';

// Date range parameters
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-d');

// Format dates for display
$startFormatted = date('d F Y', strtotime($start));
$endFormatted = date('d F Y', strtotime($end));

// ========== FINANCIAL METRICS ==========
$totalSales = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total_price) AS total FROM orders 
    WHERE DATE(order_date) BETWEEN '$start' AND '$end' AND status='Completed'
"))['total'] ?? 0;

$totalSubtotal = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(subtotal) AS total FROM orders 
    WHERE DATE(order_date) BETWEEN '$start' AND '$end' AND status='Completed'
"))['total'] ?? 0;

$totalShipping = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(shipping_fee) AS total FROM orders 
    WHERE DATE(order_date) BETWEEN '$start' AND '$end' AND status='Completed'
"))['total'] ?? 0;

// ========== ORDER METRICS ==========
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count FROM orders 
    WHERE DATE(order_date) BETWEEN '$start' AND '$end'
"))['count'] ?? 0;

$completedOrders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count FROM orders 
    WHERE DATE(order_date) BETWEEN '$start' AND '$end' AND status='Completed'
"))['count'] ?? 0;

$pendingOrders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count FROM orders 
    WHERE DATE(order_date) BETWEEN '$start' AND '$end' AND status='Pending'
"))['count'] ?? 0;

$cancelledOrders = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count FROM orders 
    WHERE DATE(order_date) BETWEEN '$start' AND '$end' AND status='Cancelled'
"))['count'] ?? 0;

$avgOrderValue = $completedOrders > 0 ? ($totalSales / $completedOrders) : 0;

// ========== CUSTOMER METRICS ==========
$totalCustomers = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count FROM users WHERE Role='customer'
"))['count'] ?? 0;

$newCustomers = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) AS count FROM users 
    WHERE Role='customer' AND DATE(CreatedAt) BETWEEN '$start' AND '$end'
"))['count'] ?? 0;

$activeCustomers = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(DISTINCT user_id) AS count FROM orders 
    WHERE DATE(order_date) BETWEEN '$start' AND '$end'
"))['count'] ?? 0;

// ========== PRODUCT METRICS ==========
$totalProductsSold = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(oi.quantity) AS total
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.status='Completed' AND DATE(o.order_date) BETWEEN '$start' AND '$end'
"))['total'] ?? 0;

// Top 10 Products
$topProducts = mysqli_query($conn, "
    SELECT p.name, p.price, SUM(oi.quantity) AS qty_sold, SUM(oi.total) AS revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.status='Completed' AND DATE(o.order_date) BETWEEN '$start' AND '$end'
    GROUP BY p.product_id
    ORDER BY qty_sold DESC
    LIMIT 10
");

// Low Stock Products
$lowStockProducts = mysqli_query($conn, "
    SELECT name, stock, price 
    FROM products 
    WHERE stock < 10 AND stock > 0
    ORDER BY stock ASC
    LIMIT 10
");

// ========== BUILD HTML REPORT ==========
$html = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; }
        h1 { text-align: center; color: #2C2C2C; border-bottom: 3px solid #8B7355; padding-bottom: 10px; }
        h2 { color: #8B7355; border-bottom: 2px solid #E5E5E5; padding-bottom: 5px; margin-top: 20px; }
        h3 { color: #555; font-size: 12pt; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { background: #8B7355; color: white; padding: 8px; text-align: left; }
        td { padding: 8px; border-bottom: 1px solid #E5E5E5; }
        tr:nth-child(even) { background: #F9F7F4; }
        .summary-box { background: #F9F7F4; padding: 15px; margin: 10px 0; border-left: 4px solid #8B7355; }
        .metric { display: inline-block; width: 48%; margin: 5px 0; }
        .metric-label { font-weight: bold; color: #555; }
        .metric-value { font-size: 14pt; color: #8B7355; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { text-align: center; margin-top: 30px; font-size: 9pt; color: #999; }
    </style>
</head>
<body>

<h1>LABU SAYONG - SALES REPORT</h1>
<p style='text-align: center; color: #666;'>Period: <strong>$startFormatted</strong> to <strong>$endFormatted</strong></p>
<p style='text-align: center; color: #999; font-size: 9pt;'>Generated on " . date('d F Y, H:i:s') . "</p>

<h2>Executive Summary</h2>
<div class='summary-box'>
    This report provides a comprehensive analysis of sales performance for Labu Sayong during the specified period. 
    Total revenue of <strong>RM " . number_format($totalSales, 2) . "</strong> was generated from <strong>$completedOrders completed orders</strong> 
    out of $totalOrders total orders placed.
</div>

<h2>Financial Overview</h2>
<table>
    <tr>
        <td class='metric-label'>Total Revenue (Completed)</td>
        <td class='text-right metric-value'>RM " . number_format($totalSales, 2) . "</td>
    </tr>
    <tr>
        <td class='metric-label'>Product Sales</td>
        <td class='text-right'>RM " . number_format($totalSubtotal, 2) . "</td>
    </tr>
    <tr>
        <td class='metric-label'>Shipping Fees</td>
        <td class='text-right'>RM " . number_format($totalShipping, 2) . "</td>
    </tr>
    <tr>
        <td class='metric-label'>Average Order Value</td>
        <td class='text-right'>RM " . number_format($avgOrderValue, 2) . "</td>
    </tr>
</table>

<h2>Order Statistics</h2>
<table>
    <tr>
        <td class='metric-label'>Total Orders</td>
        <td class='text-right'><strong>$totalOrders</strong></td>
    </tr>
    <tr>
        <td class='metric-label'>Completed Orders</td>
        <td class='text-right' style='color: #10B981;'><strong>$completedOrders</strong></td>
    </tr>
    <tr>
        <td class='metric-label'>Pending Orders</td>
        <td class='text-right' style='color: #F59E0B;'><strong>$pendingOrders</strong></td>
    </tr>
    <tr>
        <td class='metric-label'>Cancelled Orders</td>
        <td class='text-right' style='color: #EF4444;'><strong>$cancelledOrders</strong></td>
    </tr>
    <tr>
        <td class='metric-label'>Total Products Sold</td>
        <td class='text-right'><strong>$totalProductsSold units</strong></td>
    </tr>
</table>

<h2>Customer Metrics</h2>
<table>
    <tr>
        <td class='metric-label'>Total Registered Customers</td>
        <td class='text-right'><strong>$totalCustomers</strong></td>
    </tr>
    <tr>
        <td class='metric-label'>Active Customers (Made Purchase)</td>
        <td class='text-right'><strong>$activeCustomers</strong></td>
    </tr>
    <tr>
        <td class='metric-label'>New Customers Registered</td>
        <td class='text-right'><strong>$newCustomers</strong></td>
    </tr>
</table>

<h2>Top 10 Best-Selling Products</h2>
<table>
    <tr>
        <th width='5%'>#</th>
        <th width='45%'>Product Name</th>
        <th width='15%' class='text-center'>Qty Sold</th>
        <th width='15%' class='text-right'>Price</th>
        <th width='20%' class='text-right'>Revenue</th>
    </tr>";

$rank = 1;
while ($p = mysqli_fetch_assoc($topProducts)) {
    $html .= "
    <tr>
        <td class='text-center'>$rank</td>
        <td>{$p['name']}</td>
        <td class='text-center'><strong>{$p['qty_sold']}</strong></td>
        <td class='text-right'>RM " . number_format($p['price'], 2) . "</td>
        <td class='text-right'><strong>RM " . number_format($p['revenue'], 2) . "</strong></td>
    </tr>";
    $rank++;
}

$html .= "</table>

<h2>Low Stock Alert</h2>
<table>
    <tr>
        <th width='5%'>#</th>
        <th width='55%'>Product Name</th>
        <th width='20%' class='text-center'>Stock</th>
        <th width='20%' class='text-right'>Price</th>
    </tr>";

$lowStockCount = 0;
while ($ls = mysqli_fetch_assoc($lowStockProducts)) {
    $lowStockCount++;
    $stockColor = $ls['stock'] < 5 ? 'color: #EF4444;' : 'color: #F59E0B;';
    $html .= "
    <tr>
        <td class='text-center'>$lowStockCount</td>
        <td>{$ls['name']}</td>
        <td class='text-center'><strong style='$stockColor'>{$ls['stock']} units</strong></td>
        <td class='text-right'>RM " . number_format($ls['price'], 2) . "</td>
    </tr>";
}


$html .= "</table>


<div class='footer'>
    <strong>LABU SAYONG</strong> - Traditional Malaysian Ceramics<br>
    Confidential Report for Internal Use Only
</div>

</body>
</html>
";

// Generate PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Labu_Sayong_Report_{$start}_to_{$end}.pdf", ["Attachment" => true]);
exit;

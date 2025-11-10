<?php
require_once '../../includes/config.php';


// session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'staff';
$session_id = intval($_GET['session_id'] ?? 0);

if ($session_id <= 0) {
    die("<div class='container py-5 text-center'><h4>❌ Invalid chat session.</h4></div>");
}

// Fetch chat session
$sessionQuery = mysqli_query($conn, "
    SELECT s.*, p.name AS product_name, p.image AS product_image, u.FullName AS customer_name
    FROM chat_sessions s
    JOIN products p ON s.product_id = p.product_id
    JOIN users u ON s.customer_id = u.id
    WHERE s.session_id = '$session_id'
");
$session = mysqli_fetch_assoc($sessionQuery);

// Auto-assign if not yet assigned (for staff only)
if ($role !== 'admin' && empty($session['assigned_staff_id'])) {
    mysqli_query($conn, "UPDATE chat_sessions SET assigned_staff_id='$staff_id' WHERE session_id='$session_id'");
}

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = mysqli_real_escape_string($conn, trim($_POST['message']));
    $receiver_id = $session['customer_id'];

    $stmt = mysqli_prepare($conn, "
        INSERT INTO chats (session_id, sender_id, receiver_id, message, is_read, is_read_by_staff)
        VALUES (?, ?, ?, ?, 0, 0)
    ");
    mysqli_stmt_bind_param($stmt, "iiis", $session_id, $staff_id, $receiver_id, $msg);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    exit(header("Location: chat-view.php?session_id=$session_id"));
}

$productImage = !empty($session['product_image'])
    ? base_url($session['product_image'])
    : base_url('assets/img/no_image.png');

include '../../template/header.php';
include '../../template/sidebar.php';
?>

<main class="app-main">
    <div class="app-content p-4">
        <div class="container-fluid">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-warning text-dark d-flex align-items-center">
                    <img src="<?= $productImage ?>" class="rounded me-3" style="width:50px;height:50px;object-fit:cover;">
                    <div>
                        <h6 class="fw-bold mb-0">Product: <?= htmlspecialchars($session['product_name']) ?></h6>
                        <small>Customer: <?= htmlspecialchars($session['customer_name']) ?></small>
                    </div>
                </div>

                <div class="card-body bg-light" style="height:420px; overflow-y:auto;" id="chatBox">
                    <!-- Chat messages will be loaded here -->
                </div>

                <form method="POST" id="chatForm" class="card-footer d-flex gap-2 border-0 p-3">
                    <input type="text" name="message" id="messageInput" class="form-control rounded-pill" placeholder="Type your message..." required>
                    <button type="submit" class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    const chatBox = document.getElementById('chatBox');
    const sessionId = <?= $session_id ?>;

    function loadChats() {
        fetch('chat-fetch.php?session_id=' + sessionId)
            .then(response => response.text())
            .then(html => {
                chatBox.innerHTML = html;
                chatBox.scrollTop = chatBox.scrollHeight;
            });
    }

    // Initial load
    loadChats();

    // Refresh every 3 seconds
    setInterval(loadChats, 3000);
</script>

<?php include '../../template/footer.php'; ?>
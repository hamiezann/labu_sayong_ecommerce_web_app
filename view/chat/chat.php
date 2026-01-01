<?php
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$session_id = intval($_GET['session_id'] ?? 0);
$product_id = intval($_GET['product_id'] ?? 0); // NEW

$isStaff = is_staff($user_id);

// Fetch session only if $session_id exists
$session = null;
if ($session_id > 0) {
    $sessionQuery = mysqli_query($conn, "
        SELECT s.*, 
               p.name AS product_name, 
               p.image AS product_image, 
               u.FullName AS customer_name,
               (SELECT FullName FROM users WHERE id = s.assigned_staff_id) AS assigned_staff_name
        FROM chat_sessions s
        JOIN products p ON s.product_id = p.product_id
        JOIN users u ON s.customer_id = u.id
        WHERE s.session_id = '$session_id'
    ");

    if ($sessionQuery && mysqli_num_rows($sessionQuery) > 0) {
        $session = mysqli_fetch_assoc($sessionQuery);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = trim($_POST['message'] ?? '');
    if ($msg === '') {
        header("Location: chat.php?session_id=" . $session_id . "&product_id=" . $product_id);
        exit();
    }

    // Create session if it doesn't exist yet
    // if (!$session && $product_id > 0) {
    if (!$session && $product_id > 0 && $msg !== '') {
        mysqli_query($conn, "
            INSERT INTO chat_sessions (product_id, customer_id)
            VALUES ('$product_id', '$user_id')
        ");
        $session_id = mysqli_insert_id($conn);

        // Fetch the session after creation
        $sessionQuery = mysqli_query($conn, "
            SELECT s.*, 
                   p.name AS product_name, 
                   p.image AS product_image, 
                   u.FullName AS customer_name,
                   (SELECT FullName FROM users WHERE id = s.assigned_staff_id) AS assigned_staff_name
            FROM chat_sessions s
            JOIN products p ON s.product_id = p.product_id
            JOIN users u ON s.customer_id = u.id
            WHERE s.session_id = '$session_id'
        ");
        $session = mysqli_fetch_assoc($sessionQuery);
    }

    $msg = mysqli_real_escape_string($conn, $msg);

    $customer_id = $session['customer_id'];
    $assigned_staff_id = $session['assigned_staff_id'];

    if ($isStaff && empty($assigned_staff_id)) {
        mysqli_query($conn, "
            UPDATE chat_sessions 
            SET assigned_staff_id='$user_id' 
            WHERE session_id='$session_id'
        ");
        $assigned_staff_id = $user_id;
    }

    $receiver_id = ($user_id == $customer_id) ? ($assigned_staff_id ?: $user_id) : $customer_id;

    $stmt = mysqli_prepare($conn, "
        INSERT INTO chats (session_id, sender_id, receiver_id, message, is_read, is_read_by_staff)
        VALUES (?, ?, ?, ?, 0, 0)
    ");
    mysqli_stmt_bind_param($stmt, "iiis", $session_id, $user_id, $receiver_id, $msg);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: chat.php?session_id=" . $session_id);
    exit();
}

// Product image for UI
$productImage = ($session && !empty($session['product_image']))
    ? base_url($session['product_image'])
    : base_url('assets/img/no_image.png');


// Fetch messages
// $chatQuery = [];
$chatQuery = null;
if ($session) {
    $chatQuery = mysqli_query($conn, "
        SELECT c.*, u.FullName AS sender_name 
        FROM chats c
        JOIN users u ON c.sender_id = u.id
        WHERE c.session_id = '$session_id'
        ORDER BY c.created_at ASC
    ");
}

include '../customer/header.php';

?>

<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header text-dark d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #edc493 0%, #c9b59c 100%);">
            <div class="d-flex align-items-center">
                <img src="<?= $productImage ?>" class="rounded me-3" style="width:50px;height:50px;object-fit:cover;">
                <div>
                    <?php if ($session): ?>
                        <h6 class="fw-bold mb-0">Product: <?= htmlspecialchars($session['product_name']) ?></h6>
                    <?php else: ?>
                        <h6 class="fw-bold mb-0">Start a new conversation</h6>
                    <?php endif; ?>

                    <small>
                        <?php if ($session): ?>
                            Customer: <?= htmlspecialchars($session['customer_name']) ?><br>
                            <?php if ($session['assigned_staff_name']): ?>
                                Staff: <?= htmlspecialchars($session['assigned_staff_name']) ?>
                            <?php elseif ($isStaff): ?>
                                <span class="text-muted">(Unassigned — Reply to claim)</span>
                            <?php else: ?>
                                <span class="text-muted">(Waiting for staff reply)</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="card-body bg-" style="height:420px; overflow-y:auto;" id="chatBox">
            <?php if ($chatQuery && mysqli_num_rows($chatQuery) > 0): ?>
                <?php while ($chat = mysqli_fetch_assoc($chatQuery)): ?>
                    <div class="d-flex mb-3 <?= $chat['sender_id'] == $user_id ? 'justify-content-end' : 'justify-content-start' ?>">
                        <div class="p-3 rounded-3 shadow-sm <?= $chat['sender_id'] == $user_id ? 'back-success-custom text-white' : 'back-warning-custom ' ?>" style="max-width:70%;">
                            <small class="d-block fw-semibold mb-1"><?= htmlspecialchars($chat['sender_name']) ?></small>
                            <div><?= nl2br(htmlspecialchars($chat['message'])) ?></div>
                            <div class="text-end">
                                <small class="text-muted" style="font-size:0.75rem;"><?= date('H:i', strtotime($chat['created_at'])) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <form method="POST" class="card-footer d-flex gap-2 border-0 p-3 align-items-center">
            <input type="text"
                name="message"
                class="form-control rounded-pill"
                placeholder="Type your message..."
                required>

            <button type="submit"
                class="btn btn-message rounded-pill px-4 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-send-fill"></i>
                <span>Send</span>
            </button>
        </form>

    </div>
</div>

<script>
    // Auto-scroll to bottom
    const chatBox = document.getElementById('chatBox');
    chatBox.scrollTop = chatBox.scrollHeight;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../customer/footer.php'; ?>
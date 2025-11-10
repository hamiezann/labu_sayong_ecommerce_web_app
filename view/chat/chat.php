<?php
require_once '../../includes/config.php';


// ✅ Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$session_id = intval($_GET['session_id'] ?? 0);

if ($session_id <= 0) {
    echo "<div class='container py-5 text-center'><h4>❌ Invalid chat session.</h4></div>";
    include '../customer/footer.php';
    exit();
}

/* -----------------------------------
   Detect staff role
------------------------------------ */
// function is_staff($uid)
// {
//     global $conn;
//     $res = mysqli_query($conn, "SELECT role FROM users WHERE id='$uid'");
//     if ($res && $row = mysqli_fetch_assoc($res)) {
//         return in_array(strtolower($row['role']), ['staff', 'manager', 'admin']);
//     }
//     return false;
// }
$isStaff = is_staff($user_id);

/* -----------------------------------
   Fetch chat session + product info
------------------------------------ */
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

if (!$sessionQuery || mysqli_num_rows($sessionQuery) == 0) {
    echo "<div class='container py-5 text-center'><h4>❌ Chat session not found.</h4></div>";
    include '../customer/footer.php';
    exit();
}

$session = mysqli_fetch_assoc($sessionQuery);

// Product image
$productImage = !empty($session['product_image'])
    ? base_url($session['product_image'])
    : base_url('assets/img/no_image.png');

/* -----------------------------------
   Handle message send
------------------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = mysqli_real_escape_string($conn, trim($_POST['message']));
    $customer_id = $session['customer_id'];
    $assigned_staff_id = $session['assigned_staff_id'];

    // 🟡 Auto-assign staff if none yet
    if ($isStaff && empty($assigned_staff_id)) {
        mysqli_query($conn, "
            UPDATE chat_sessions 
            SET assigned_staff_id='$user_id' 
            WHERE session_id='$session_id'
        ");
        $assigned_staff_id = $user_id;
    }

    // 🧠 Determine receiver
    if ($user_id == $customer_id) {
        // Customer sending → receiver is assigned staff
        $receiver_id = $assigned_staff_id ?: $user_id; // temp self until staff replies
    } else {
        // Staff sending → receiver is customer
        $receiver_id = $customer_id;
    }

    // ✅ Insert message with receiver_id (to avoid foreign key errors)
    $stmt = mysqli_prepare($conn, "
        INSERT INTO chats (session_id, sender_id, receiver_id, message, is_read, is_read_by_staff)
        VALUES (?, ?, ?, ?, 0, 0)
    ");
    mysqli_stmt_bind_param($stmt, "iiis", $session_id, $user_id, $receiver_id, $msg);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Redirect to refresh messages
    header("Location: chat.php?session_id=" . $session_id);
    exit();
}

/* -----------------------------------
   Fetch chat messages
------------------------------------ */
$chatQuery = mysqli_query($conn, "
    SELECT c.*, u.FullName AS sender_name 
    FROM chats c
    JOIN users u ON c.sender_id = u.id
    WHERE c.session_id = '$session_id'
    ORDER BY c.created_at ASC
");

include '../customer/header.php';
?>

<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4">
        <!-- 🟡 Header -->
        <div class="card-header bg-warning text-dark d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img src="<?= $productImage ?>" class="rounded me-3" style="width:50px;height:50px;object-fit:cover;">
                <div>
                    <h6 class="fw-bold mb-0">Product: <?= htmlspecialchars($session['product_name']) ?></h6>
                    <small>
                        Customer: <?= htmlspecialchars($session['customer_name']) ?><br>
                        <?php if ($session['assigned_staff_name']): ?>
                            Staff: <?= htmlspecialchars($session['assigned_staff_name']) ?>
                        <?php elseif ($isStaff): ?>
                            <span class="text-muted">(unassigned — reply to claim)</span>
                        <?php else: ?>
                            <span class="text-muted">(waiting for staff reply)</span>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>

        <!-- 💬 Chat Body -->
        <div class="card-body bg-light" style="height:420px; overflow-y:auto;" id="chatBox">
            <?php while ($chat = mysqli_fetch_assoc($chatQuery)): ?>
                <div class="d-flex mb-3 <?= $chat['sender_id'] == $user_id ? 'justify-content-end' : 'justify-content-start' ?>">
                    <div class="p-3 rounded-3 shadow-sm <?= $chat['sender_id'] == $user_id ? 'bg-success text-white' : 'bg-white' ?>" style="max-width:70%;">
                        <small class="d-block fw-semibold mb-1"><?= htmlspecialchars($chat['sender_name']) ?></small>
                        <div><?= nl2br(htmlspecialchars($chat['message'])) ?></div>
                        <div class="text-end">
                            <small class="text-muted" style="font-size:0.75rem;"><?= date('H:i', strtotime($chat['created_at'])) ?></small>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- ✍️ Message Input -->
        <form method="POST" class="card-footer d-flex gap-2 border-0 p-3">
            <input type="text" name="message" class="form-control rounded-pill" placeholder="Type your message..." required>
            <button type="submit" class="btn btn-success rounded-pill px-4">
                <i class="bi bi-send-fill"></i>
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
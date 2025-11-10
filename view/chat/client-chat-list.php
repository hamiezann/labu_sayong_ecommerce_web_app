<?php
include '../../includes/config.php';

// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page = 'chat';
$subPage = 'client-chat-list';
$pageName = 'My Chats';

// Fetch chat sessions for this customer
$query = mysqli_query($conn, "
    SELECT 
        s.session_id,
        p.name AS product_name,
        p.image AS product_image,
        (SELECT message FROM chats WHERE session_id = s.session_id ORDER BY created_at DESC LIMIT 1) AS last_message,
        (SELECT created_at FROM chats WHERE session_id = s.session_id ORDER BY created_at DESC LIMIT 1) AS last_message_time,
        (SELECT FullName FROM users WHERE id = s.assigned_staff_id) AS assigned_staff_name
    FROM chat_sessions s
    JOIN products p ON s.product_id = p.product_id
    WHERE s.customer_id = '$user_id'
    ORDER BY s.created_at DESC
");

include '../customer/header.php';
?>

<main class="app-main">
    <div class="app-content p-2 p-lg-4" style="min-height: 80vh">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="fw-bold mb-0"><i class="bi bi-chat-dots-fill me-2 text-primary"></i><?= $pageName ?></h3>
            </div>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-0" style="background-color:#f8f9fa;">
                    <?php if (mysqli_num_rows($query) === 0): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-dots fs-1"></i>
                            <p class="mt-3">You have no active chats yet.<br>Start one from a product page!</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php while ($row = mysqli_fetch_assoc($query)):
                                // $productImg = !empty($row['product_image'])
                                //     ? htmlspecialchars($row['product_image'])
                                //     : '../../assets/img/no_image.png';

                                $imagePath = !empty($row['product_image'])
                                    ? base_url($row['product_image'])
                                    : base_url('assets/img/no_image.png');
                                $lastMessage = htmlspecialchars($row['last_message'] ?? 'No messages yet');
                                $time = $row['last_message_time']
                                    ? date('H:i', strtotime($row['last_message_time']))
                                    : '';
                                $staff = htmlspecialchars($row['assigned_staff_name'] ?? 'Waiting for staff');
                            ?>
                                <a href="chat.php?session_id=<?= $row['session_id'] ?>"
                                    class="list-group-item list-group-item-action border-0 px-3 py-3 d-flex align-items-center"
                                    style="transition: background 0.2s ease;"
                                    onmouseover="this.style.background='#eef5ff'"
                                    onmouseout="this.style.background='transparent'">
                                    <div class="flex-shrink-0">
                                        <img src="<?= $imagePath ?>"
                                            alt="product"
                                            width="55"
                                            height="55"
                                            class="rounded-circle border shadow-sm"
                                            style="object-fit:cover;">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($row['product_name']) ?></h6>
                                            <small class="text-muted"><?= $time ?></small>
                                        </div>
                                        <div class="text-truncate text-secondary" style="max-width: 280px;">
                                            <?= $lastMessage ?>
                                        </div>
                                        <small class="text-muted fst-italic"><?= $staff ?></small>
                                    </div>
                                </a>
                                <hr class="m-0 text-muted opacity-25">
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../customer/footer.php'; ?>
<?php
include '../../includes/config.php';
include '../../template/header.php';
$page = 'chat-list';
$subPage = 'chat-list';
$pageName = 'User Inquiries';

include '../../template/sidebar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$staff_id = $_SESSION['user_id'];
$role = $userInfo['Role']; // 'admin' or 'staff'



// Staff list (for admin assignment)
$staffList = [];
if ($role === 'admin') {
    $staffQuery = mysqli_query($conn, "SELECT id, FullName FROM users WHERE Role IN ('staff','admin')");
    while ($s = mysqli_fetch_assoc($staffQuery)) {
        $staffList[] = $s;
    }
}

//  Everyone (admin + staff) can see all chat sessions
$where = "1 = 1";

// Fetch chat sessions with product and user info
$query = mysqli_query($conn, "
    SELECT 
        s.*, 
        p.name AS product_name,
        u.FullName AS customer_name,
        (SELECT FullName FROM users WHERE id = s.assigned_staff_id) AS assigned_staff_name,
        (SELECT message FROM chats WHERE session_id = s.session_id ORDER BY created_at DESC LIMIT 1) AS last_message,
        (SELECT created_at FROM chats WHERE session_id = s.session_id ORDER BY created_at DESC LIMIT 1) AS last_message_time
    FROM chat_sessions s
    JOIN products p ON s.product_id = p.product_id
    JOIN users u ON s.customer_id = u.id
    WHERE $where
    ORDER BY s.created_at DESC
");
?>

<main class="app-main">
    <div class="app-content p-4">
        <div class="container-fluid">
            <h1 class="mb-4">

                <i class="bi bi-chat-dots me-2"></i><?= $pageName ?>
            </h1>
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success_message'];
                                                    unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error_message'];
                                                unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>
            <div class="card shadow-sm">
                <div class="card-header text-white d-flex justify-content-between" style="background-color: #74512D">
                    <h5 class="mb-0"><i class="bi bi-envelope-open me-2"></i>Recent Chats</h5>
                    <?php if ($role === 'admin'): ?>
                        <small class="text-light">Admin mode: full control</small>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <table id="chatTable" class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Last Message</th>
                                <th>Status</th>
                                <th>Assigned Staff</th>
                                <th>Last Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            while ($row = mysqli_fetch_assoc($query)): ?>
                                <?php
                                $assigned = $row['assigned_staff_id'] ? true : false;
                                $statusBadge = $assigned
                                    ? '<span class="badge bg-success">Claimed</span>'
                                    : '<span class="badge bg-secondary">Unassigned</span>';

                                //  Only admin or assigned staff can access chat
                                $canChat = ($role === 'admin' || $row['assigned_staff_id'] == $staff_id);
                                ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                    <td><?= htmlspecialchars($row['product_name']) ?></td>
                                    <td><?= htmlspecialchars($row['last_message'] ?? '—') ?></td>
                                    <td><?= $statusBadge ?></td>
                                    <td>
                                        <?php if ($role === 'admin'): ?>
                                            <select class="form-select form-select-sm staff-assign" data-session="<?= $row['session_id'] ?>">
                                                <option value="">Unassigned</option>
                                                <?php foreach ($staffList as $staff): ?>
                                                    <option value="<?= $staff['id'] ?>" <?= ($row['assigned_staff_id'] == $staff['id']) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($staff['FullName']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else: ?>
                                            <?= htmlspecialchars($row['assigned_staff_name'] ?? '-') ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $row['last_message_time'] ? date('Y-m-d H:i', strtotime($row['last_message_time'])) : '-' ?></td>
                                    <td>
                                        <?php if ($canChat): ?>
                                            <a href="chat-view.php?session_id=<?= $row['session_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-reply"></i> Chat
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Not authorized">
                                                <i class="bi bi-lock"></i> Restricted
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($role === 'admin'): ?>
                                            <a href="chat-delete.php?session_id=<?= $row['session_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this chat?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../../template/footer.php'; ?>

<script>
    new DataTable('#chatTable');

    // Handle staff assignment
    document.querySelectorAll('.staff-assign').forEach(select => {
        select.addEventListener('change', function() {
            const sessionId = this.dataset.session;
            const staffId = this.value;

            fetch('chat-assign.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'session_id=' + sessionId + '&staff_id=' + staffId
                })
                .then(res => res.text())
                .then(res => {
                    if (res.trim() === 'OK') {
                        alert('Staff updated successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + res);
                    }
                });
        });
    });
</script>
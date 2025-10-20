<?php
include '../../includes/config.php';
include '../../template/header.php';
include '../../template/sidebar.php';

$page = 'chat';
$subPage = 'chat-list';
$pageName = 'User Inquiries';
?>

<main class="app-main">
    <div class="app-content p-4">
        <div class="container-fluid">
            <h1 class="mb-4"><i class="bi bi-chat-dots me-2"></i><?= $pageName ?></h1>

            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-envelope-open me-2"></i>Recent Chats</h5>
                </div>
                <div class="card-body">
                    <table id="chatTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Received At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td>User <?= $i ?></td>
                                    <td>“Hello, I have a question about product <?= $i ?>.”</td>
                                    <td><span class="badge bg-success">Read</span></td>
                                    <td><?= date('Y-m-d H:i:s') ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary"><i class="bi bi-reply"></i></a>
                                    </td>
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
    new DataTable('#chatTable');
</script>
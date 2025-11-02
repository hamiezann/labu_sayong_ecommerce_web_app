<?php
include '../../includes/config.php';

$page = 'staff';
$subPage = 'staff-profile';
$pageName = 'Edit My Profile';

// 🔒 Ensure staff is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

// Get logged-in staff info
$staff_id = intval($_SESSION['user_id']);
$staff = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$staff_id' AND Role='staff' LIMIT 1"));

if (!$staff) {
    $_SESSION['error_message'] = "❌ Staff profile not found.";
    header("Location: ../auth/login.php");
    exit();
}

// --- Handle Update Form ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_staff'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $imagePath = $staff['Image']; // keep old image by default

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../../uploads/staff/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Delete old image if exists
            if (!empty($staff['Image']) && file_exists("../../" . $staff['Image'])) {
                unlink("../../" . $staff['Image']);
            }
            $imagePath = "uploads/staff/" . $fileName;
        }
    }

    // Update DB
    $update = "UPDATE users 
               SET FullName='$fullname', Email='$email', Image='$imagePath', UpdatedAt=NOW() 
               WHERE id='$staff_id' AND Role='staff'";
    if (mysqli_query($conn, $update)) {
        mysqli_query($conn, "INSERT INTO logs (user_id, action) VALUES ('$staff_id', 'Updated own profile')");
        $_SESSION['success_message'] = "✅ Your profile has been updated successfully!";
        header("Location: staff-profile.php");
        exit();
    } else {
        $_SESSION['error_message'] = "❌ Failed to update your profile.";
    }
}

include '../../template/header.php';
include '../../template/sidebar.php';
?>

<main class="app-main">
    <div class="app-content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-4"><i class="bi bi-person-badge me-2"></i><?= $pageName ?></h1>
                <button class="btn btn-warning px-3 changePasswordBtn"
                    title="Edit Password">
                    <i class="bi bi-shield-lock me-1"></i> Change Password
                </button>
            </div>

            <!-- Alerts -->
            <?php if (isset($_SESSION['success_message'])) : ?>
                <div class="alert alert-success"><?= $_SESSION['success_message'];
                                                    unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])) : ?>
                <div class="alert alert-danger"><?= $_SESSION['error_message'];
                                                unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <!-- Profile Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-fill me-2"></i>My Profile</h5>

                </div>
                <div class="card-body">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-3 text-center">
                            <img src="<?= base_url(!empty($staff['Image']) ? $staff['Image'] : 'assets/img/preview.jpg') ?>"
                                alt="Profile Image"
                                class="rounded-circle shadow"
                                width="150" height="150">
                        </div>

                        <div class="col-md-9">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="fullname" value="<?= htmlspecialchars($staff['FullName']) ?>" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($staff['Email']) ?>" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Profile Image</label>
                                    <input type="file" name="image" accept="image/*" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Role</label>
                                    <input type="text" value="<?= ucfirst($staff['Role']) ?>" class="form-control" readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Created At</label>
                                    <input type="text" value="<?= date("d M Y, h:i A", strtotime($staff['CreatedAt'])) ?>" class="form-control" readonly>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="update_staff" class="btn btn-primary px-4">
                                        <i class="bi bi-save me-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- change password modal -->
<div class="modal fade" id="chgPassModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" action="../auth/update-password.php" class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="user_id">
                <div class="row">
                    <label class="form-label fw-semibold">Current Password</label>
                    <div class="input-group">
                        <input type="password" name="current_password" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary toggle-password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <label class="form-label fw-semibold">New Password</label>
                    <div class="input-group">
                        <input type="password" name="new_password" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary toggle-password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary toggle-password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-12 text-center m-3">
                <button type="submit" class="btn btn-warning px-4">
                    <i class="bi bi-save me-1"></i> Update Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });

    document.querySelectorAll('.changePasswordBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = document.querySelector('#chgPassModal form');

            if (form) form.reset();
            new bootstrap.Modal(document.getElementById('chgPassModal')).show();
        })
    })
</script>
<?php include '../../template/footer.php'; ?>
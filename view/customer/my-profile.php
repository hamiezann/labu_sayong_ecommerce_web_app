<?php
ob_start();

require_once '../../includes/config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);

require_once 'header.php';

// Image path
$imagePath = !empty($user['Image'])
    ? base_url($user['Image'])
    : base_url('assets/img/no_image.png');

// ✅ Update profile form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $imagePath = $user['Image'];


    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../../uploads/staff/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Delete old image if exists
            if (!empty($user['Image']) && file_exists("../../" . $user['Image'])) {
                unlink("../../" . $user['Image']);
            }
            $imagePath = "uploads/staff/" . $fileName;
        }
    }

    // Update DB
    $update = "UPDATE users 
               SET FullName='$fullname', Email='$email', Image='$imagePath', UpdatedAt=NOW() , address='$address', phone='$phone'
               WHERE id='$user_id'";
    if (mysqli_query($conn, $update)) {
        mysqli_query($conn, "INSERT INTO logs (user_id, action) VALUES ('$user_id', 'Updated own profile')");
        $_SESSION['success_message'] = "✅ Your profile has been updated successfully!";
        header("Location: my-profile.php");
        exit();
    } else {
        $_SESSION['error_message'] = "❌ Failed to update your profile.";
    }
}
?>

<div class="container py-5">
    <div class="card border-0 shadow-lg p-4 rounded-4 position-relative">
        <!-- Edit Icon -->
        <button id="editBtn" class="btn btn-success position-absolute top-0 end-0 m-3 rounded-circle shadow-sm"
            title="Edit Profile" style="width: 40px; height: 40px;">
            <i class="bi bi-pencil-fill"></i>
        </button>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success_message'];
                                                unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error_message'];
                                            unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div class="row align-items-center">
            <!-- Profile Image -->
            <div class="col-md-4 text-center mb-4 mb-md-0">
                <img src="<?= $imagePath ?>" alt="Profile Picture"
                    class="rounded-circle shadow-lg img-fluid"
                    style="width: 220px; height: 220px; object-fit: cover; border: 5px solid #f8f9fa;">

                <!-- Image Upload (hidden until edit mode) -->
                <div class="edit-mode d-none mt-3">
                    <input type="file" name="image" form="profileForm" class="form-control">
                </div>
            </div>

            <!-- Profile Details -->
            <div class="col-md-8">
                <h3 class="fw-semibold mb-3 text-primary">My Profile</h3>
                <hr class="mb-4">

                <form id="profileForm" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold">Full Name:</div>
                        <div class="col-sm-8">
                            <span class="view-mode"><?= htmlspecialchars($user['FullName']) ?></span>
                            <input type="text" name="fullname" class="form-control edit-mode d-none"
                                value="<?= htmlspecialchars($user['FullName']) ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold">Email:</div>
                        <div class="col-sm-8">
                            <span class="view-mode"><?= htmlspecialchars($user['Email']) ?></span>
                            <input type="email" name="email" class="form-control edit-mode d-none"
                                value="<?= htmlspecialchars($user['Email']) ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold">Address:</div>
                        <div class="col-sm-8">
                            <span class="view-mode"><?= htmlspecialchars($user['address']) ?></span>
                            <input type="text" name="address" class="form-control edit-mode d-none"
                                value="<?= htmlspecialchars($user['address']) ?>">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold">Phone No:</div>
                        <div class="col-sm-8">
                            <span class="view-mode"><?= htmlspecialchars($user['phone']) ?></span>
                            <input type="number" name="phone" class="form-control edit-mode d-none"
                                value="<?= htmlspecialchars($user['phone']) ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold">Role:</div>
                        <div class="col-sm-8 text-capitalize"><?= htmlspecialchars($user['Role']) ?></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold">Member Since:</div>
                        <div class="col-sm-8"><?= date('d M Y', strtotime($user['CreatedAt'])) ?></div>
                    </div>

                    <div class="text-end mt-4 edit-mode d-none">
                        <button type="button" id="cancelEdit" class="btn btn-secondary me-2">Cancel</button>
                        <button type="submit" name="update_user" class="btn btn-success px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <hr class="my-5">

        <!-- Change Password Section -->
        <div class="mt-4" id="passSectionVisibility">
            <h4 class="fw-semibold text-primary mb-3">
                <i class="bi bi-lock-fill me-2"></i>Change Password
            </h4>

            <form action="../auth/update-password.php" method="POST" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Current Password</label>
                    <div class="input-group">
                        <input type="password" name="current_password" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary toggle-password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">New Password</label>
                    <div class="input-group">
                        <input type="password" name="new_password" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary toggle-password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" name="confirm_password" class="form-control" required>
                        <button type="button" class="btn btn-outline-secondary toggle-password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="col-12 text-end mt-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toggle Edit + Password Visibility -->
<script>
    // Toggle profile edit mode
    document.getElementById('editBtn').addEventListener('click', function() {
        document.querySelectorAll('.view-mode').forEach(el => el.classList.toggle('d-none'));
        document.querySelectorAll('.edit-mode').forEach(el => el.classList.toggle('d-none'));
        const passSectionVisibility = document.getElementById('passSectionVisibility');
        passSectionVisibility.hidden = !passSectionVisibility.hidden;
        this.classList.toggle('btn-outline-success');
        this.classList.toggle('btn-success');

    });

    document.getElementById('cancelEdit').addEventListener('click', function() {
        document.querySelectorAll('.view-mode').forEach(el => el.classList.remove('d-none'));
        document.querySelectorAll('.edit-mode').forEach(el => el.classList.add('d-none'));
        document.getElementById('editBtn').classList.add('btn-success');
        document.getElementById('editBtn').classList.remove('btn-outline-success');
    });

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
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php
require_once 'footer.php';
ob_end_flush();
?>
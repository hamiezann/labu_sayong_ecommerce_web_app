<?php
include '../../includes/config.php';

$page = 'admin';
$subPage = 'manage-customer';
$pageName = 'Manage Customer';

// fetch full cust list
$cust_lists = mysqli_query($conn, "SELECT * FROM users WHERE role = 'customer' ORDER BY FullName DESC");

// add new cust
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_customer'])) {

    $defaultpassword = "123456";
    $fullname = mysqli_real_escape_string($conn, $_POST['add_fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['add_email']);
    $password = password_hash($defaultpassword, PASSWORD_DEFAULT);
    $imagePath = null;
    $role = "customer";

    if (!empty($_FILES['add_image']['name'])) {
        $targetDir = "../../uploads/products/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['add_image']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['add_image']['tmp_name'], $targetFile)) {
            $imagePath = "uploads/products/" . $fileName;
        }
    }

    $insert = "INSERT INTO users (FullName, Email, Password, Image, Role) VALUES ('$fullname', '$email', '$password', '$imagePath', '$role')";
    if (mysqli_query($conn, $insert)) {
        $uid = $_SESSION['user_id'];
        mysqli_query($conn, "INSERT INTO logs (user_id, action) VALUES ('$uid', 'Added Customer: $fullname')");
        $_SESSION['success_message'] = "✅ Customer added successfully!";
        header("Location: manage-customer.php");
        exit();
    }
}

// edit customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_customer'])) {
    $id = $_POST['id'];
    $fullname = mysqli_real_escape_string($conn, $_POST['edit_name']);
    $email = mysqli_real_escape_string($conn, $_POST['edit_email']);

    $updateQuery = "UPDATE users SET FullName='$fullname', Email='$email'";

    // Handle image update
    if (!empty($_FILES['edit_image']['name'])) {
        $targetDir = "../../uploads/products/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . '_' . basename($_FILES['edit_image']['name']);
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($_FILES['edit_image']['tmp_name'], $targetFile)) {
            $imagePath = "uploads/products/" . $fileName;
            $updateQuery .= ", image='$imagePath'";
        }
    }

    $updateQuery .= " WHERE id='$id'";
    if (mysqli_query($conn, $updateQuery)) {
        $uid = $_SESSION['user_id'];
        mysqli_query($conn, "INSERT INTO logs (user_id, action) VALUES ('$uid', 'Updated customer: $fullname')");
        $_SESSION['success_message'] = "✅ Customer updated successfully!";
        header("Location: manage-customer.php");
        exit();
    }
}

// delete customer
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']); // sanitize ID

    // Step 1: Get customer info
    $result = mysqli_query($conn, "SELECT FullName, Image FROM users WHERE id = '$id' AND Role = 'customer' LIMIT 1");
    if (!$result || mysqli_num_rows($result) === 0) {
        $_SESSION['error_message'] = "❌ Customer not found or already deleted.";
        header("Location: manage-customer.php");
        exit();
    }

    $row = mysqli_fetch_assoc($result);
    $name = $row['FullName'];
    $imagePath = "../../" . $row['Image'];

    if (!empty($row['Image']) && file_exists($imagePath)) {
        unlink($imagePath);
    }

    mysqli_query($conn, "DELETE FROM logs WHERE user_id = '$id'");
    mysqli_query($conn, "DELETE FROM chats WHERE sender_id = '$id' OR receiver_id = '$id'");
    mysqli_query($conn, "DELETE FROM users WHERE id = '$id'");

    $uid = $_SESSION['user_id'] ?? 0;
    $action = mysqli_real_escape_string($conn, "Deleted customer: $name");
    mysqli_query($conn, "INSERT INTO logs (user_id, action) VALUES ('$uid', '$action')");

    $_SESSION['success_message'] = "🗑️ Customer '$name' deleted successfully with related data!";
    header("Location: manage-customer.php");
    exit();
}


include '../../template/header.php';
include '../../template/sidebar.php';
?>

<main class="app-main">
    <div class="app-content p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-item-center mb-4">

                <h1 class="mb-4"><i class="bi bi-people-fill me-2"></i><?= $pageName ?></h1>
                <button class="btn btn-md btn-success addBtn"><i class="bi bi-plus-lg me-2"></i> Add Customer</button>
            </div>

            <!-- Alert message -->
            <?php if (isset($_SESSION['success_message'])) : ?>
                <div class="alert alert-success"><?= $_SESSION['success_message'];
                                                    unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>

            <!-- customer list -->
            <div class="card-shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Customer List</h5>
                </div>
                <div class="card-body">
                    <table id="staffTable" class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Profile Image</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1;
                            while ($row = mysqli_fetch_assoc($cust_lists)) : ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <?php if ($row['Image']) : ?>
                                            <img src="<?= base_url($row['Image']) ?>" width="50" class="rounded">
                                        <?php else: ?>
                                            <span class="text-muted">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['FullName']) ?></td>
                                    <td><?= htmlspecialchars($row['Email']) ?></td>
                                    <td><?= date("d M Y, h:i A", strtotime($row['CreatedAt'])) ?> </td>
                                    <td><?= date("d M Y, h:i A", strtotime($row['UpdatedAt'])) ?> </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary editBtn"
                                            data-id="<?= htmlspecialchars($row['id'], ENT_QUOTES) ?>"
                                            data-image="<?= htmlspecialchars($row['Image'], ENT_QUOTES) ?>"
                                            data-fullname="<?= htmlspecialchars($row['FullName'], ENT_QUOTES) ?>"
                                            data-email="<?= htmlspecialchars($row['Email'], ENT_QUOTES) ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="?delete=<?= urlencode($row['id']) ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
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

<!-- edit modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Edit Customer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">Image (optional)</label>
                    <input type="file" id="edit_image" name="edit_image" accept="image/*" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="edit_name" id="edit_fullname" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="edit_email" id="edit_email" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update_customer" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- add modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Customer Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="add_id">
                <div class="mb-3">
                    <label class="form-label">Image (optional)</label>
                    <input type="file" id="add_image" name="add_image" accept="image/*" class="form-control">

                    <!-- Hidden field + preview -->
                    <input type="hidden" id="add_image_old" name="add_image_old">
                    <img id="edit_image_preview" src="<?= base_url('assets/img/preview.jpg') ?>" width="100" alt="Preview" class="rounded mt-2">
                </div>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="add_fullname" id="add_fullname" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="add_email" id="add_email" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="create_customer" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../template/footer.php'; ?>
<script>
    new DataTable('#staffTable');

    document.addEventListener('DOMContentLoaded', () => {

        // --- Handle Edit Staff ---
        document.querySelectorAll('.editBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                const editId = document.getElementById('edit_id');
                const editFullname = document.getElementById('edit_fullname');
                const editEmail = document.getElementById('edit_email');
                const editImage = document.getElementById('edit_image');
                const editImageOld = document.getElementById('edit_image_old');
                const editImagePreview = document.getElementById('edit_image_preview');

                // Only proceed if all fields exist
                if (!editId || !editFullname || !editEmail) return;

                // Fill input fields
                editId.value = btn.dataset.id || '';
                editFullname.value = btn.dataset.fullname || '';
                editEmail.value = btn.dataset.email || '';

                // Image preview setup
                if (editImageOld && editImagePreview) {
                    if (btn.dataset.image && btn.dataset.image.trim() !== '') {
                        editImageOld.value = btn.dataset.image;
                        editImagePreview.src = '<?= base_url('uploads/') ?>' + btn.dataset.image;
                    } else {
                        editImageOld.value = '';
                        editImagePreview.src = '<?= base_url('assets/img/preview.jpg') ?>';
                    }
                }

                new bootstrap.Modal(document.getElementById('editModal')).show();
            });
        });

        // --- Handle Add Staff ---
        document.querySelectorAll('.addBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                const form = document.querySelector('#addModal form');
                const editImageOld = document.getElementById('add_image_old');
                const editImagePreview = document.getElementById('add_image_preview');

                // Reset form safely
                if (form) form.reset();

                // Reset image preview
                if (editImageOld) editImageOld.value = '';
                if (editImagePreview) {
                    editImagePreview.src = '<?= base_url('assets/img/preview.jpg') ?>';
                }

                new bootstrap.Modal(document.getElementById('addModal')).show();
            });
        });
    });
</script>
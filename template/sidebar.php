<!--begin::Sidebar-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="<?= base_url('./') ?>" class="brand-link">
            <img
                src="<?= base_url('assets/img/AdminLTELogo.png') ?>"
                alt="AdminLTE Logo"
                class="brand-image opacity-75 shadow" />
            <span class="brand-text fw-light"><?= $userInfo['Role'] === 'admin' ? 'Admin Dashboard' : 'Staff Dashboard' ?></span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul
                class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                role="navigation"
                aria-label="Main navigation"
                data-accordion="false"
                id="navigation">

                <!-- main -->
                <li class="nav-header">MAIN</li>
                <?php if ($userInfo && $userInfo['Role'] === 'admin'): ?>
                    <!-- dashboard -->
                    <li class="nav-item">
                        <a href="<?= base_url('view/admin/dashboard.php') ?>" class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-palette"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('view/staff/manage-product.php') ?>" class="nav-link <?= $subPage == 'manage-product' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-box-seam"></i>
                            <p>Manage Products</p>
                        </a>
                    </li>

                    <!-- admin management -->
                    <li class="nav-item <?= $page == 'admin' ? 'menu-open' : '' ?>">
                        <a href="#" class="nav-link <?= $page == 'admin' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-people"></i>
                            <p>
                                User Management
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">

                            <li class="nav-item">
                                <a href="<?= base_url('view/admin/manage-staff.php') ?>" class="nav-link <?= $subPage == 'manage-staff' ? 'active' : '' ?>">
                                    <i class="nav-icon bi bi-circle"></i>
                                    <p>Manage Staff</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="<?= base_url('view/admin/manage-customer.php') ?>" class="nav-link <?= $subPage == 'manage-customer' ? 'active' : '' ?>">
                                    <i class="nav-icon bi bi-circle"></i>
                                    <p>Manage Client</p>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
                <?php if ($userInfo && $userInfo['Role'] === 'staff'): ?>
                    <li class="nav-item">
                        <a href="<?= base_url('view/admin/manage-customer.php') ?>" class="nav-link <?= $subPage == 'manage-customer' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Manage Client</p>
                        </a>
                    </li>
                    <!-- product management -->
                    <li class="nav-item">
                        <a href="<?= base_url('view/staff/manage-product.php') ?>" class="nav-link <?= $subPage == 'manage-product' ? 'active' : '' ?>">
                            <i class="nav-icon bi bi-box-seam"></i>
                            <p>Manage Products</p>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- order management -->
                <li class="nav-item <?= $page == 'order' ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $page == 'order' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-cart-check"></i>
                        <p>
                            Order Management
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('view/staff/order-list.php') ?>" class="nav-link <?= $subPage == 'view-orders' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>View Orders</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('view/staff/order-history.php') ?>" class="nav-link <?= $subPage == 'order-history' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Order History</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- chat / inquiries -->
                <li class="nav-item <?= $page == 'chat' ? 'menu-open' : '' ?>">
                    <a href="#" class="nav-link <?= $page == 'chat' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-chat-dots"></i>
                        <p>
                            Chat / Inquiries
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('view/staff/chat-list.php') ?>" class="nav-link <?= $subPage == 'chat-list' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Manage Chats</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('view/staff/chat-history.php') ?>" class="nav-link <?= $subPage == 'chat-history' ? 'active' : '' ?>">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Chat History</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </nav>
    </div>
</aside>
<!--end::Sidebar-->
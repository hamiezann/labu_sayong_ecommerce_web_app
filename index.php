<?php
require_once 'includes/config.php';
// include 'assets/css/client.css';
$productList = mysqli_query($conn, "SELECT * FROM products LIMIT 4");
$title = "Labu Sayong - Home";
if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];
    $userData = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$userId'"));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $title ?> | Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/client.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('index.php') ?>">
                <i class="bi bi-flower3 me-2"></i>Labu Sayong
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link active" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('view/shop-listing.php') ?>">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>

                    <!-- Cart Icon -->
                    <li class="nav-item ms-3">
                        <a href="#" class="nav-link position-relative">
                            <i class="bi bi-bag" style="font-size: 1.3rem;"></i>
                            <span class="cart-badge">0</span>
                        </a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item ms-2">
                            <button class="btn btn-login" data-bs-toggle="modal" data-bs-target="#modalSignOut">
                                Logout
                            </button>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-2">
                            <a href="<?= base_url('view/auth/login.php') ?>" class="btn btn-login">
                                Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero-section" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1>Let's explore<br>Traditional<br><em style="font-style: italic;">Crafts</em></h1>
                        <p>Discover authentic handcrafted pottery<br>made with heritage and passion</p>
                        <a href="#products" class="btn btn-explore">
                            Explore Now
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image">
                        <div class="hero-image-wrapper">
                            <img src="assets/img/hero.jpg" alt="Labu Sayong" style="width: 85%; height: 85%; object-fit: cover; border-radius: 50%;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURED PRODUCTS -->
    <section class="products-section" id="products">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Classes</h2>
                <p class="section-subtitle">Handpicked traditional crafts from master artisans</p>
            </div>
            <div class="row g-4">
                <?php
                if (mysqli_num_rows($productList) > 0):
                    while ($product = mysqli_fetch_assoc($productList)):
                        $imagePath = !empty($product['image']) ? base_url($product['image']) : base_url('assets/img/no_image.png');
                ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="card product-card">
                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                <div class="card-body">
                                    <h5><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Traditional Craft</p>
                                    <p class="product-price">RM <?= number_format($product['price'], 2) ?></p>
                                    <a href="#" class="btn btn-view">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php
                    endwhile;
                else:
                    ?>
                    <div class="col-12">
                        <div class="text-center p-5">
                            <i class="bi bi-inbox" style="font-size: 4rem; color: #cbd5e0;"></i>
                            <p class="text-muted mt-3">No products available yet. Check back soon!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Why Choose Us</h2>
                <p class="section-subtitle">Experience the best of Malaysian traditional craftsmanship</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-award feature-icon"></i>
                        <h4>Handcrafted</h4>
                        <p>Every piece is meticulously handcrafted by skilled artisans using traditional techniques passed down through generations.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-patch-check feature-icon"></i>
                        <h4>Authentic</h4>
                        <p>100% authentic Malaysian pottery, sourced directly from master craftsmen in Perak, the heartland of Labu Sayong.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <i class="bi bi-heart feature-icon"></i>
                        <h4>Heritage</h4>
                        <p>Support traditional craftsmanship and help preserve Malaysia's rich cultural heritage for future generations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT SECTION -->
    <section class="contact-section" id="contact">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Get In Touch</h2>
                <p class="section-subtitle">We'd love to hear from you. Reach out for inquiries or custom orders.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="contact-card">
                        <h3 class="mb-4">Contact Information</h3>

                        <div class="contact-item">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-geo-alt-fill"></i>
                                <div>
                                    <strong>Address</strong>
                                    <p class="mb-0 mt-1">Kampung Kepala Bendang<br>Kuala Kangsar, 33000 Perak</p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-telephone-fill"></i>
                                <div>
                                    <strong>Phone</strong>
                                    <p class="mb-0 mt-1">+60 12-345 6789</p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-envelope-fill"></i>
                                <div>
                                    <strong>Email</strong>
                                    <p class="mb-0 mt-1">info@labusayong.com</p>
                                </div>
                            </div>
                        </div>

                        <div class="contact-item">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-clock-fill"></i>
                                <div>
                                    <strong>Business Hours</strong>
                                    <p class="mb-0 mt-1">Mon - Sat: 9:00 AM - 6:00 PM<br>Sunday: Closed</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="contact-form">
                        <h3 class="mb-4" style="color: #2d3748;">Send Us a Message</h3>
                        <form>
                            <div class="mb-3">
                                <label class="form-label fw-500">Your Name</label>
                                <input type="text" class="form-control" placeholder="John Doe" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-500">Email Address</label>
                                <input type="email" class="form-control" placeholder="john@example.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-500">Message</label>
                                <textarea class="form-control" rows="5" placeholder="How can we help you?" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-send">
                                <i class="bi bi-send me-2"></i> Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="bi bi-palette me-2"></i> Labu Sayong</h5>
                    <p class="small">Preserving the art of traditional Malaysian pottery and bringing authentic craftsmanship to your home.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#home">Home</a></li>
                        <li class="mb-2"><a href="#products">Products</a></li>
                        <li class="mb-2"><a href="#about">About</a></li>
                        <li class="mb-2"><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Follow Us</h5>
                    <div class="d-flex">
                        <a href="#" class="social-btn"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-whatsapp"></i></a>
                        <a href="#" class="social-btn"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
            </div>
            <hr style="border-color: rgba(255,255,255,0.1); margin: 2rem 0 1rem;">
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> Labu Sayong. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Smooth Scroll -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>

</body>

</html>

<?php include 'includes/footer.php'; ?>
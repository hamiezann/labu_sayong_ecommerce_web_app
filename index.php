<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
include 'view/customer/header.php';
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $productList = mysqli_query($conn, "
        SELECT p.*, 
               CASE WHEN w.product_id IS NOT NULL THEN 1 ELSE 0 END AS in_wishlist
        FROM products p
        LEFT JOIN wishlist w 
        ON p.product_id = w.product_id AND w.user_id = '$user_id'
        LIMIT 12
    ");
} else {
    $productList = mysqli_query($conn, "SELECT p.*, 0 AS in_wishlist FROM products p LIMIT 12");
}

$title = "Labu Sayong - Home";

?>
<style>
    .carousel-control-prev,
    .carousel-control-next {
        top: 50%;
        transform: translateY(-50%);
        width: 50px;
        height: 100px;
        background-color: rgba(118, 112, 112, 0.4);
        border-radius: 6px;
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        background-color: rgba(118, 112, 112, 0.4);
    }
</style>


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
            <h2 class="section-title">Our Products</h2>
            <p class="section-subtitle">Handpicked traditional crafts from master artisans</p>
        </div>

        <?php if (mysqli_num_rows($productList) > 0): ?>
            <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">

                    <?php
                    $products = [];
                    while ($product = mysqli_fetch_assoc($productList)) {
                        $products[] = $product;
                    }

                    $chunked = array_chunk($products, 4); // show 4 per slide
                    $active = 'active';
                    foreach ($chunked as $group):
                    ?>
                        <div class="carousel-item <?= $active ?>">
                            <div class="row g-4">
                                <?php foreach ($group as $product):
                                    $imagePath = !empty($product['image'])
                                        ? base_url($product['image'])
                                        : base_url('assets/img/no_image.png');
                                ?>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="card product-card h-100 shadow-sm">
                                            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="card-img-top" style="height:200px; object-fit:cover;">
                                            <div class="card-body d-flex flex-column justify-content-between">
                                                <div>
                                                    <h5><?= htmlspecialchars($product['name']) ?></h5>
                                                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Traditional Craft</p>
                                                    <p class="product-price">RM <?= number_format($product['price'], 2) ?></p>
                                                </div>
                                                <div class="d-flex align-items-center gap-3 mt-3">
                                                    <a href="<?= base_url('view/customer/product-detail.php?id=' . $product['product_id']) ?>" class="btn btn-view">View Details</a>
                                                    <button class="btn btn-icon-fav wishlist-btn"
                                                        data-product-id="<?= $product['product_id'] ?>">
                                                        <i class="bi <?= $product['in_wishlist'] ? 'bi-heart-fill text-danger' : 'bi-heart' ?> fs-5"></i>
                                                    </button>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php
                        $active = ''; // only first active
                    endforeach;
                    ?>
                </div>

                <!-- Carousel controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next bg-grey" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        <?php else: ?>
            <div class="text-center p-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #cbd5e0;"></i>
                <p class="text-muted mt-3">No products available yet. Check back soon!</p>
            </div>
        <?php endif; ?>

        <!-- “View More” button -->
        <?php if (mysqli_num_rows($productList) >= 6): // if more than 8 products 
        ?>
            <div class="text-center mt-5">
                <!-- <a href="<?= base_url('view/shop-listing.php') ?>" class="btn btn-send btn-lg"> -->
                <a href="<?= base_url('view/shop-listing.php') ?>" class="btn btn-view">
                    View More Products
                </a>
            </div>
        <?php endif; ?>
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
                        <button type="submit" class="btn btn-view">
                            <i class=" bi bi-send me-2"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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


    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const icon = this.querySelector('i');

            fetch('function/toggle-wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `product_id=${productId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (data.action === 'added') {
                            icon.classList.remove('bi-heart');
                            icon.classList.add('bi-heart-fill', 'text-danger');
                            Swal.fire({
                                icon: 'success',
                                title: 'Added to Wishlist!',
                                timer: 1200,
                                showConfirmButton: false
                            });
                        } else {
                            icon.classList.remove('bi-heart-fill', 'text-danger');
                            icon.classList.add('bi-heart');
                            Swal.fire({
                                icon: 'info',
                                title: 'Removed from Wishlist',
                                timer: 1200,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Login Required',
                            text: data.message,
                        });
                    }
                })
                .catch(async (err) => {
                    const text = await err?.text?.();
                    console.error('Wishlist toggle error:', err, text);
                });
        });
    });
</script>


</body>

</html>

<?php

include 'view/customer/footer.php';
?>
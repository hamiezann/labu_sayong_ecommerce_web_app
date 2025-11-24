  <!-- FOOTER -->
  <footer>
      <div class="container">
          <div class="row">
              <div class="col-md-4 mb-4">
                  <h5><i class="bi bi-palette me-2"></i> CRAFTEASE</h5>
                  <p class="small">Preserving the art of traditional Malaysian pottery and bringing authentic craftsmanship to your home.</p>
              </div>
              <div class="col-md-4 mb-4">
                  <h5>Quick Links</h5>
                  <ul class="list-unstyled">
                      <li class="mb-2"><a href="<?= base_url('index.php') ?>">Home</a></li>
                      <li class="mb-2"><a href="<?= base_url('view/shop-listing.php') ?>">Products</a></li>
                      <li class="mb-2"><a href="<?= base_url('index.php') ?>#about">About</a></li>
                      <li class="mb-2"><a href="<?= base_url('index.php') ?>#contact">Contact</a></li>
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
              <p class="mb-0">&copy; <?= date('Y') ?> CRAFTEASE. All rights reserved.</p>
          </div>
      </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
      function setRange(min, max) {
          document.querySelector('input[name="min_price"]').value = min;
          document.querySelector('input[name="max_price"]').value = max;
          document.getElementById('filterForm').submit();
      }

      // Smooth scroll for anchor links
      document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
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
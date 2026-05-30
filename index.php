<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kyutopia Products</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<!-- ══════════════════════════════════════
     LOGO SECTION
══════════════════════════════════════ -->
<div class="logo-section">
    <img src="images/kyutopia.png" alt="kyutopia">
</div>

<!-- ══════════════════════════════════════
     MAIN CONTAINER
══════════════════════════════════════ -->
<div class="container">

    <!-- CART ICON -->
    <div class="cart-icon-wrapper">
        <a href="cart.html" class="cart-icon-btn">
            🛒 Panier
            <span id="cart-badge">0</span>
        </a>
    </div>

    <!-- ══════════════════════════════════
         INTRO
    ══════════════════════════════════ -->
    <div class="intro">
        <h2>Kyutopia Products</h2>
        <p>
            Order handmade clay pieces or request a custom creation.
            Thank you for supporting Kyutopia!
        </p>

        <h2>Why choose Kyutopia?</h2>
        <ul class="features-list">
            <li>Handmade clay accessories</li>
            <li>Anime inspired designs</li>
            <li>Custom clay creations</li>
            <li>Affordable handmade products</li>
        </ul>
    </div>

    <!-- ══════════════════════════════════
         SEARCH & FILTER
    ══════════════════════════════════ -->
    <div class="search-filter-section">

        <input
            type="text"
            id="search-input"
            placeholder="🔍 Search a product...">

        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="lighter">Lighters</button>
            <button class="filter-btn" data-filter="keychain">Keychains</button>
            <button class="filter-btn" data-filter="necklace">Necklaces</button>
            <button class="filter-btn" data-filter="earring">Earrings</button>
            <button class="filter-btn" data-filter="ashtray">Ashtrays</button>
        </div>

    </div>

    <!-- ══════════════════════════════════
         PRODUCT GRID
    ══════════════════════════════════ -->
    <h2 id="products">Select products to Order</h2>

    <div class="products" id="product-grid">

        <?php
        require 'db.php';
        $products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($products as $p):
        ?>

        <div class="product-card"
             data-name="<?= strtolower($p['name']) ?>"
             data-category="<?= $p['category'] ?>"
             data-img="<?= $p['image'] ?>"
             data-price="<?= $p['price'] ?>"
             data-price-display="<?= $p['price'] ?> DT">

            <img src="<?= $p['image'] ?>" alt="<?= $p['name'] ?>">
            <h3><?= $p['name'] ?></h3>
            <p class="price"><?= $p['price'] ?> DT</p>

            <!-- class changed: was "btn add-to-cart-btn" → now just "add-to-cart-btn"
                 so the global .btn link styles no longer conflict with this button -->
            <button class="add-to-cart-btn"
                    data-name="<?= $p['name'] ?>"
                    data-price="<?= $p['price'] ?>"
                    data-img="<?= $p['image'] ?>">
                Add to Cart
            </button>

        </div>

        <?php endforeach; ?>

    </div>


    <!-- ══════════════════════════════════
         CUSTOM SECTION
    ══════════════════════════════════ -->
    <section class="custom-section">

        <div class="custom-section__header">
            <span class="custom-section__icon">🎨</span>
            <div>
                <h2>Want a Custom Clay Item?</h2>
                <p>Upload a reference photo and describe what you have in mind.</p>
            </div>
        </div>

        <form class="custom-form" action="order.html" method="get">

            <div class="custom-form__group">
                <label class="custom-form__label" for="custom-file">Upload reference image</label>
                <input class="custom-form__file" type="file" id="custom-file" accept="image/*" required>
            </div>

            <div class="custom-form__group">
                <label class="custom-form__label" for="custom-desc">Description</label>
                <textarea class="custom-form__textarea" id="custom-desc" rows="4"
                    placeholder="Describe your custom item — colors, size, character..."></textarea>
            </div>

            <button class="custom-form__submit" type="submit">
                ✉️ Send Custom Request
            </button>

        </form>

    </section>


    <!-- ══════════════════════════════════
         CONTACT SECTION
    ══════════════════════════════════ -->
    <div class="contact-section">
        <h2 class="contact-section__title">Contact us for more information</h2>
        <div class="contact-section__links">
            <a href="https://www.instagram.com/kyutopia__" target="_blank"
               class="contact-btn contact-btn--instagram">
                📸 Instagram
            </a>
            <a href="https://Wa.me/+21654461357" target="_blank"
               class="contact-btn contact-btn--whatsapp">
                💬 WhatsApp
            </a>
        </div>
    </div>

</div><!-- /.container -->


<!-- ══════════════════════════════════════
     SCROLL TO TOP
══════════════════════════════════════ -->
<div id="scroll-top-btn">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="none"
         stroke="white" stroke-width="2.5"
         stroke-linecap="round" stroke-linejoin="round">
        <polyline points="18 15 12 9 6 15"/>
    </svg>
</div>


<!-- Footer -->
<div style="background:#f9f9f9;padding:16px;text-align:center;font-size:12px;color:#aaa">
    © Kyutopia — Handmade with love 🌸
</div>


<!-- ══════════════════════════════════════
     MODAL
     class changed: was "btn" → now "modal-order-btn"
     so the product .btn styles don't bleed in here
══════════════════════════════════════ -->
<div id="modal-overlay">
    <div id="modal-box">

        <button id="modal-close">✕</button>

        <img id="modal-img" src="" alt="">
        <h3 id="modal-name"></h3>
        <p id="modal-price"></p>
        <a id="modal-order-btn" href="cart.html" class="modal-order-btn">🛒 Add to Cart</a>

        <hr style="margin:20px 0;border:none;border-top:1px solid #eee">

        <!-- ══════════════════════════════
             REVIEWS (inside modal)
             All inline styles moved to CSS — scoped to #review-form-wrapper
        ══════════════════════════════ -->
        <div id="modal-reviews-section">

            <div id="modal-average"></div>

            <div id="modal-reviews-list"></div>

            <div id="review-form-wrapper">
                <h4>✍️ Laisser un avis</h4>

                <input type="text" id="review-name" placeholder="Ton prénom">

                <div id="star-selector">
                    <span class="star" data-value="1">☆</span>
                    <span class="star" data-value="2">☆</span>
                    <span class="star" data-value="3">☆</span>
                    <span class="star" data-value="4">☆</span>
                    <span class="star" data-value="5">☆</span>
                </div>

                <textarea id="review-comment" rows="3"
                    placeholder="Ton commentaire..."></textarea>

                <button id="review-submit-btn">Envoyer l'avis</button>

                <p id="review-feedback">✅ Avis envoyé, merci !</p>
            </div>

        </div>

    </div>
</div>


<script src="filter.js"></script>
<script src="modal.js"></script>
<script src="cart.js"></script>
<script src="reviews.js"></script>

<script>
    const scrollBtn = document.getElementById('scroll-top-btn');

    window.addEventListener('scroll', function () {
        scrollBtn.style.display = window.scrollY > 300 ? 'flex' : 'none';
    });

    scrollBtn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>

</body>
</html>
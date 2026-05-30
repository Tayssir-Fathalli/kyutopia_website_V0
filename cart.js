// ── CHARGER LE PANIER DEPUIS localStorage ──────────────────────
// localStorage garde les données même si on ferme le navigateur
// Si rien n'existe encore → on part d'un tableau vide []
let cart = JSON.parse(localStorage.getItem('kyutopia_cart')) || [];

// ── SAUVEGARDER LE PANIER ──────────────────────────────────────
function saveCart() {
    localStorage.setItem('kyutopia_cart', JSON.stringify(cart));
}

// ── METTRE À JOUR LE BADGE (le chiffre sur l'icône 🛒) ─────────
function updateBadge() {
    const badge = document.getElementById('cart-badge');
    if (!badge) return;

    // On compte le nombre total d'articles (somme des quantités)
    const total = cart.reduce(function(sum, item) {
        return sum + item.quantity;
    }, 0);

    badge.textContent = total;

    // Si panier vide → on cache le badge
    badge.style.display = total === 0 ? 'none' : 'inline-block';
}

// ── AJOUTER UN PRODUIT AU PANIER ──────────────────────────────
function addToCart(name, price, img) {

    // On cherche si ce produit est déjà dans le panier
    const existing = cart.find(function(item) {
        return item.name === name;
    });

    if (existing) {
        // Produit déjà présent → on augmente juste la quantité
        existing.quantity += 1;
    } else {
        // Nouveau produit → on l'ajoute au tableau
        cart.push({
            name:     name,
            price:    parseFloat(price),  // on s'assure que c'est un nombre
            img:      img,
            quantity: 1
        });
    }

    saveCart();
    updateBadge();

    // Petit feedback visuel pour l'utilisateur
    showToast(name + ' ajouté au panier !');
}

// ── TOAST (notification temporaire en bas de l'écran) ──────────
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'cart-toast';
    toast.textContent = message;
    document.body.appendChild(toast);

    // Après 2.5 secondes → on supprime le toast
    setTimeout(function() {
        toast.remove();
    }, 2500);
}

// ── ÉCOUTER LES BOUTONS "Add to Cart" ─────────────────────────
const addButtons = document.querySelectorAll('.add-to-cart-btn');

addButtons.forEach(function(btn) {
    btn.addEventListener('click', function(event) {
        // Empêcher l'ouverture du modal au clic
        event.stopPropagation();

        const name  = btn.getAttribute('data-name');
        const price = btn.getAttribute('data-price');
        const img   = btn.getAttribute('data-img');

        addToCart(name, price, img);
    });
});

// ── INITIALISER LE BADGE AU CHARGEMENT DE LA PAGE ─────────────
updateBadge();
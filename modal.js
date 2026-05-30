// On récupère tous les éléments du modal
const overlay    = document.getElementById('modal-overlay');
const modalImg   = document.getElementById('modal-img');
const modalName  = document.getElementById('modal-name');
const modalPrice = document.getElementById('modal-price');
const closeBtn   = document.getElementById('modal-close');

// On récupère toutes les cartes produit
const modalCards = document.querySelectorAll('.product-card');

// ─── FONCTION : OUVRIR LE MODAL ────────────────────────────────
function openModal(card) {

    const img   = card.getAttribute('data-img');
    const name  = card.getAttribute('data-name');
    const price = card.getAttribute('data-price-display');

    modalImg.src              = img;
    modalImg.alt              = name;
    modalName.textContent     = name;
    modalPrice.textContent    = price;

    overlay.classList.add('active');

    // ✅ Charger les avis et initialiser le formulaire
    loadReviews(name);
    initReviewForm(name);
    initStars();
}

// ─── FONCTION : FERMER LE MODAL ────────────────────────────────
function closeModal() {
  overlay.classList.remove('active');
}

// ─── ÉCOUTER LE CLIC SUR CHAQUE CARTE ─────────────────────────
modalCards.forEach(function(card) {

  card.addEventListener('click', function(event) {

    // Si le clic est sur le bouton "Order Now", on ne fait rien
    // (on laisse le lien fonctionner normalement)
    if (event.target.classList.contains('btn')) {
      return;
    }

    // Sinon on ouvre le modal avec les données de cette carte
    openModal(card);
  });

});

// ─── FERMER EN CLIQUANT SUR LE FOND SOMBRE ────────────────────
overlay.addEventListener('click', function(event) {

  // event.target = l'élément exact sur lequel on a cliqué
  // Si c'est l'overlay lui-même (le fond) et pas la boîte blanche → on ferme
  if (event.target === overlay) {
    closeModal();
  }

});

// ─── FERMER AVEC LA TOUCHE ÉCHAP ──────────────────────────────
document.addEventListener('keydown', function(event) {

  if (event.key === 'Escape') {
    closeModal();
  }

});

// ─── FERMER EN CLIQUANT SUR LE BOUTON ✕ ───────────────────────
closeBtn.addEventListener('click', closeModal);
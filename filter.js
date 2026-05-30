// On récupère les éléments HTML dont on a besoin
const searchInput = document.getElementById('search-input');
const filterButtons = document.querySelectorAll('.filter-btn');
const productCards = document.querySelectorAll('.product-card');

// La catégorie active au départ = "all" (tout afficher)
let activeCategory = 'all';

// ─── FONCTION PRINCIPALE ───────────────────────────────────────
// Elle est appelée à chaque fois que l'utilisateur tape ou clique
function applyFilters() {

  // On lit ce que l'utilisateur a tapé, en minuscules
  const searchText = searchInput.value.toLowerCase();

  // On parcourt chaque carte produit
  productCards.forEach(function(card) {

    // On lit les attributs data-* de cette carte
    const name     = card.getAttribute('data-name');      // ex: "kirby necklace"
    const category = card.getAttribute('data-category');  // ex: "necklace"

    // Condition 1 : le nom contient le texte recherché ?
    const matchesSearch = name.includes(searchText);

    // Condition 2 : la catégorie correspond au filtre actif ?
    const matchesCategory = (activeCategory === 'all') || (category === activeCategory);

    // Si les DEUX conditions sont vraies → on affiche la carte
    if (matchesSearch && matchesCategory) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }

  });
}

// ─── ÉCOUTER LA BARRE DE RECHERCHE ────────────────────────────
// À chaque lettre tapée, on relance applyFilters()
searchInput.addEventListener('input', applyFilters);

// ─── ÉCOUTER LES BOUTONS DE FILTRE ────────────────────────────
filterButtons.forEach(function(btn) {
  btn.addEventListener('click', function() {

    // On retire la classe "active" de tous les boutons
    filterButtons.forEach(function(b) {
      b.classList.remove('active');
    });

    // On met "active" seulement sur le bouton cliqué
    btn.classList.add('active');

    // On met à jour la catégorie active
    activeCategory = btn.getAttribute('data-filter');

    // On relance le filtre
    applyFilters();

  });
});
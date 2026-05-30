// L'étoile sélectionnée (1 à 5)
let selectedStars = 0;

// ── ÉTOILES CLIQUABLES ─────────────────────────────────────────
function initStars() {
    const stars = document.querySelectorAll('.star');

    stars.forEach(function(star) {

        // Au survol → colorier les étoiles jusqu'à celle survolée
        star.addEventListener('mouseover', function() {
            const val = parseInt(star.getAttribute('data-value'));
            stars.forEach(function(s) {
                s.textContent = parseInt(s.getAttribute('data-value')) <= val ? '★' : '☆';
                s.style.color = parseInt(s.getAttribute('data-value')) <= val ? '#e91e63' : '#ccc';
            });
        });

        // Quand on quitte → revenir à la sélection actuelle
        star.addEventListener('mouseleave', function() {
            updateStarDisplay(selectedStars);
        });

        // Au clic → sauvegarder la sélection
        star.addEventListener('click', function() {
            selectedStars = parseInt(star.getAttribute('data-value'));
            updateStarDisplay(selectedStars);
        });
    });
}

// ── METTRE À JOUR L'AFFICHAGE DES ÉTOILES ─────────────────────
function updateStarDisplay(count) {
    const stars = document.querySelectorAll('.star');
    stars.forEach(function(s) {
        const val = parseInt(s.getAttribute('data-value'));
        s.textContent = val <= count ? '★' : '☆';
        s.style.color = val <= count ? '#e91e63' : '#ccc';
    });
}

// ── CHARGER LES AVIS D'UN PRODUIT ─────────────────────────────
function loadReviews(productName) {

    const list    = document.getElementById('modal-reviews-list');
    const average = document.getElementById('modal-average');

    list.innerHTML    = '<p style="color:#aaa;text-align:center">Chargement...</p>';
    average.innerHTML = '';

    // Appel AJAX vers review.php
    fetch('review.php?action=get&product=' + encodeURIComponent(productName))
        .then(function(response) { return response.json(); })
        .then(function(data) {

            // Afficher la moyenne
            if (data.count > 0) {
                const stars = '★'.repeat(Math.round(data.average)) + '☆'.repeat(5 - Math.round(data.average));
                average.innerHTML = `
                    <span style="font-size:24px;color:#e91e63">${stars}</span>
                    <span style="font-size:16px;color:#555"> ${data.average}/5 (${data.count} avis)</span>
                `;
            }

            // Afficher chaque avis
            if (data.reviews.length === 0) {
                list.innerHTML = '<p style="color:#aaa;text-align:center">Aucun avis pour ce produit. Sois le premier ! 😊</p>';
                return;
            }

            list.innerHTML = '';
            data.reviews.forEach(function(review) {
                const stars   = '★'.repeat(review.stars) + '☆'.repeat(5 - review.stars);
                const date    = new Date(review.created_at).toLocaleDateString('fr-FR');
                const div     = document.createElement('div');
                div.className = 'review-item';
                div.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <b>${review.name}</b>
                        <span style="font-size:12px;color:#aaa">${date}</span>
                    </div>
                    <div style="color:#e91e63;font-size:16px">${stars}</div>
                    <p style="margin:6px 0 0;color:#555;font-size:14px">${review.comment}</p>
                `;
                list.appendChild(div);
            });
        });
}

// ── SOUMETTRE UN AVIS ──────────────────────────────────────────
function initReviewForm(productName) {

    selectedStars = 0;
    updateStarDisplay(0);

    // Réinitialiser le formulaire
    document.getElementById('review-name').value    = '';
    document.getElementById('review-comment').value = '';
    document.getElementById('review-feedback').style.display = 'none';

    const submitBtn = document.getElementById('review-submit-btn');

    // Supprimer l'ancien écouteur pour éviter les doublons
    const newBtn = submitBtn.cloneNode(true);
    submitBtn.parentNode.replaceChild(newBtn, submitBtn);

    newBtn.addEventListener('click', function() {

        const name    = document.getElementById('review-name').value.trim();
        const comment = document.getElementById('review-comment').value.trim();

        // Validation
        if (!name || !comment || selectedStars === 0) {
            alert('Remplis tous les champs et choisis une note !');
            return;
        }

        // Préparer les données
        const formData = new FormData();
        formData.append('product', productName);
        formData.append('name',    name);
        formData.append('stars',   selectedStars);
        formData.append('comment', comment);

        // Envoyer via AJAX
        fetch('review.php?action=save', {
            method: 'POST',
            body:   formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                // Afficher le message de succès
                document.getElementById('review-feedback').style.display = 'block';
                document.getElementById('review-name').value    = '';
                document.getElementById('review-comment').value = '';
                selectedStars = 0;
                updateStarDisplay(0);

                // Recharger les avis
                loadReviews(productName);
            }
        });
    });
}
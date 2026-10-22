/**
 * Aperçu en direct de la couverture dans le formulaire d'un livre.
 * Met à jour le titre et l'auteur pendant la saisie.
 */
(function () {
    const couverture = document.querySelector('.apercu .couverture');
    if (!couverture) {
        return;
    }

    const champTitre = document.querySelector('[data-apercu="titre"]');
    const champAuteur = document.querySelector('[data-apercu="auteur"]');

    const texteTitre = couverture.querySelector('.couverture__titre');
    const texteAuteur = couverture.querySelector('.couverture__auteur');

    function mettreAJour() {
        texteTitre.textContent = champTitre.value.trim() || 'Titre du livre';

        const optionChoisie = champAuteur.options[champAuteur.selectedIndex];
        texteAuteur.textContent = champAuteur.value ? optionChoisie.text.trim() : '';
    }

    champTitre.addEventListener('input', mettreAJour);
    champAuteur.addEventListener('change', mettreAJour);

    mettreAJour();
})();

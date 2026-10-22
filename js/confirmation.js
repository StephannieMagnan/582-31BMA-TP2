/**
 * Demande une confirmation avant d'envoyer un formulaire de suppression.
 */
(function () {
    const formulaires = document.querySelectorAll('form[data-confirmation]');

    formulaires.forEach(function (formulaire) {
        formulaire.addEventListener('submit', function (evenement) {
            if (!window.confirm(formulaire.dataset.confirmation)) {
                evenement.preventDefault();
            }
        });
    });
})();

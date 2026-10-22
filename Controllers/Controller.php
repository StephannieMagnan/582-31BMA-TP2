<?php

namespace App\Controllers;

use App\Providers\Session;
use App\Providers\View;

/**
 * Classe parente des contrôleurs.
 */
abstract class Controller
{
    /**
     * Identifiant reçu dans l'adresse ou dans un formulaire POST.
     */
    protected function idRecu(): int
    {
        return (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
    }

    /**
     * Données du formulaire.
     */
    protected function donneesFormulaire(): array
    {
        $donnees = $_POST;
        unset($donnees['jeton_csrf'], $donnees['id']);

        return $donnees;
    }

    /**
     * Arrête le traitement si le formulaire ne provient pas du site.
     */
    protected function verifierJeton(string $retour): void
    {
        if (!Session::jetonCsrfValide()) {
            Session::ajouterMessage('erreur', 'Le formulaire a expiré. Envoyez-le de nouveau.');
            View::redirect($retour);
        }
    }
}

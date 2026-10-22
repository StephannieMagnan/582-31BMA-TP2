<?php

namespace App\Controllers;

use App\Models\Editeur;
use App\Providers\Session;
use App\Providers\Validator;
use App\Providers\View;

/**
 * Gestion des éditeurs.
 */
class EditeurController extends Controller
{
    /**
     * Liste des éditeurs avec leur nombre de livres.
     */
    public function index(): void
    {
        $editeur = new Editeur();

        View::render('editeur/index', [
            'menu'     => 'editeurs',
            'editeurs' => $editeur->selectAvecNombreLivres(),
        ]);
    }

    /**
     * Formulaire d'ajout.
     */
    public function create(): void
    {
        $this->afficherFormulaire([]);
    }

    /**
     * Valide puis ajoute l'éditeur.
     */
    public function store(): void
    {
        $this->verifierJeton('/editeur/create');

        $donnees = $this->donneesFormulaire();
        $erreurs = $this->valider($donnees);

        if (!empty($erreurs)) {
            $this->afficherFormulaire($donnees, $erreurs);
            return;
        }

        $editeur = new Editeur();
        $editeur->insert($donnees);

        Session::ajouterMessage('succes', 'L\'éditeur ' . trim($donnees['nom']) . ' a été ajouté.');
        View::redirect('/editeurs');
    }

    /**
     * Formulaire de modification.
     */
    public function edit(): void
    {
        $editeur = new Editeur();
        $selectId = $editeur->selectId($this->idRecu());

        if (!$selectId) {
            $this->editeurIntrouvable();
        }

        $this->afficherFormulaire($selectId);
    }

    /**
     * Valide puis modifie l'éditeur.
     */
    public function update(): void
    {
        $this->verifierJeton('/editeurs');

        $id = $this->idRecu();
        $editeur = new Editeur();

        if (!$editeur->selectId($id)) {
            $this->editeurIntrouvable();
        }

        $donnees = $this->donneesFormulaire();
        $erreurs = $this->valider($donnees);

        if (!empty($erreurs)) {
            $this->afficherFormulaire($donnees + ['id' => $id], $erreurs);
            return;
        }

        $editeur->update($donnees, $id);

        Session::ajouterMessage('succes', 'L\'éditeur ' . trim($donnees['nom']) . ' a été modifié.');
        View::redirect('/editeurs');
    }

    /**
     * Supprime l'éditeur.
     * Ses livres restent au catalogue, sans éditeur.
     */
    public function delete(): void
    {
        $this->verifierJeton('/editeurs');

        $editeur = new Editeur();
        $selectId = $editeur->selectId($this->idRecu());

        if (!$selectId) {
            $this->editeurIntrouvable();
        }

        $editeur->delete($selectId['id']);

        Session::ajouterMessage('succes', 'L\'éditeur ' . $selectId['nom'] . ' a été supprimé.');
        View::redirect('/editeurs');
    }

    /* ------------------------------------------------------------
       Outils du contrôleur
      ------------------------------------------------------------ */

    /**
     * Règles de validation du formulaire d'un éditeur.
     */
    private function valider(array $donnees): array
    {
        $validateur = new Validator($donnees);

        $validateur->champ('nom', 'Le nom')->requis()->max(100);
        $validateur->champ('ville', 'La ville')->max(80);
        $validateur->champ('site_web', 'Le site Web')->max(255)->url();

        return $validateur->erreurs();
    }

    private function editeurIntrouvable(): never
    {
        Session::ajouterMessage('erreur', 'Cet éditeur n\'existe pas ou a été supprimé.');
        View::redirect('/editeurs');
    }

    private function afficherFormulaire(array $editeur, array $erreurs = []): void
    {
        View::render('editeur/form', [
            'menu'    => 'editeurs',
            'editeur' => $editeur,
            'erreurs' => $erreurs,
        ]);
    }
}

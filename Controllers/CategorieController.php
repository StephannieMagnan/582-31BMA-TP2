<?php

namespace App\Controllers;

use App\Models\Categorie;
use App\Providers\Session;
use App\Providers\Validator;
use App\Providers\View;

/**
 * Gestion des catégories.
 */
class CategorieController extends Controller
{
    /**
     * Liste des catégories avec leur nombre de livres.
     */
    public function index(): void
    {
        $categorie = new Categorie();

        View::render('categorie/index', [
            'menu'       => 'categories',
            'categories' => $categorie->selectAvecNombreLivres(),
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
     * Valide puis ajoute la catégorie.
     */
    public function store(): void
    {
        $this->verifierJeton('/categorie/create');

        $donnees = $this->donneesFormulaire();
        $erreurs = $this->valider($donnees);

        if (!empty($erreurs)) {
            $this->afficherFormulaire($donnees, $erreurs);
            return;
        }

        $categorie = new Categorie();
        $categorie->insert($donnees);

        Session::ajouterMessage('succes', 'La catégorie ' . trim($donnees['nom']) . ' a été ajoutée.');
        View::redirect('/categories');
    }

    /**
     * Formulaire de modification.
     */
    public function edit(): void
    {
        $categorie = new Categorie();
        $selectId = $categorie->selectId($this->idRecu());

        if (!$selectId) {
            $this->categorieIntrouvable();
        }

        $this->afficherFormulaire($selectId);
    }

    /**
     * Valide puis modifie la catégorie.
     */
    public function update(): void
    {
        $this->verifierJeton('/categories');

        $id = $this->idRecu();
        $categorie = new Categorie();

        if (!$categorie->selectId($id)) {
            $this->categorieIntrouvable();
        }

        $donnees = $this->donneesFormulaire();
        $erreurs = $this->valider($donnees, $id);

        if (!empty($erreurs)) {
            $this->afficherFormulaire($donnees + ['id' => $id], $erreurs);
            return;
        }

        $categorie->update($donnees, $id);

        Session::ajouterMessage('succes', 'La catégorie ' . trim($donnees['nom']) . ' a été modifiée.');
        View::redirect('/categories');
    }

    /**
     * Supprime la catégorie.
     * Seuls ses liens dans livre_categorie disparaissent : les livres restent au catalogue.
     */
    public function delete(): void
    {
        $this->verifierJeton('/categories');

        $categorie = new Categorie();
        $selectId = $categorie->selectId($this->idRecu());

        if (!$selectId) {
            $this->categorieIntrouvable();
        }

        $categorie->delete($selectId['id']);

        Session::ajouterMessage('succes', 'La catégorie ' . $selectId['nom'] . ' a été supprimée.');
        View::redirect('/categories');
    }

    /* ------------------------------------------------------------
       Outils du contrôleur
      ------------------------------------------------------------ */

    /**
     * Règles de validation du formulaire d'une catégorie.
     */
    private function valider(array $donnees, int $id = 0): array
    {
        $categorie = new Categorie();
        $validateur = new Validator($donnees);

        $validateur->champ('nom', 'Le nom')
            ->requis()
            ->max(60)
            ->regle(fn ($nom) => !$categorie->nomExiste($nom, $id), 'Une catégorie porte déjà ce nom.');

        $validateur->champ('description', 'La description')->max(255);

        return $validateur->erreurs();
    }

    private function categorieIntrouvable(): never
    {
        Session::ajouterMessage('erreur', 'Cette catégorie n\'existe pas ou a été supprimée.');
        View::redirect('/categories');
    }

    private function afficherFormulaire(array $categorie, array $erreurs = []): void
    {
        View::render('categorie/form', [
            'menu'      => 'categories',
            'categorie' => $categorie,
            'erreurs'   => $erreurs,
        ]);
    }
}

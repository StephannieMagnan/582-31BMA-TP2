<?php

namespace App\Controllers;

use App\Models\Auteur;
use App\Providers\Session;
use App\Providers\Validator;
use App\Providers\View;

/**
 * Gestion des auteurs.
 */
class AuteurController extends Controller
{
    /**
     * GET /auteurs : liste des auteurs avec leur nombre de livres.
     */
    public function index(): void
    {
        $auteur = new Auteur();

        View::render('auteur/index', [
            'menu'    => 'auteurs',
            'auteurs' => $auteur->selectAvecNombreLivres(),
        ]);
    }

    /**
     * GET /auteur/create : formulaire d'ajout.
     */
    public function create(): void
    {
        $this->afficherFormulaire([]);
    }

    /**
     * POST /auteur/store : valide puis ajoute l'auteur.
     */
    public function store(): void
    {
        $this->verifierJeton('/auteur/create');

        $donnees = $this->donneesFormulaire();
        $erreurs = $this->valider($donnees);

        if (!empty($erreurs)) {
            $this->afficherFormulaire($donnees, $erreurs);
            return;
        }

        $auteur = new Auteur();
        $auteur->insert($donnees);

        Session::ajouterMessage('succes', $this->nomComplet($donnees) . ' a été ajouté.');
        View::redirect('/auteurs');
    }

    /**
     * Formulaire de modification.
     */
    public function edit(): void
    {
        $auteur = new Auteur();
        $selectId = $auteur->selectId($this->idRecu());

        if (!$selectId) {
            $this->auteurIntrouvable();
        }

        $this->afficherFormulaire($selectId);
    }

    /**
     * Valide puis modifie l'auteur.
     */
    public function update(): void
    {
        $this->verifierJeton('/auteurs');

        $id = $this->idRecu();
        $auteur = new Auteur();

        if (!$auteur->selectId($id)) {
            $this->auteurIntrouvable();
        }

        $donnees = $this->donneesFormulaire();
        $erreurs = $this->valider($donnees);

        if (!empty($erreurs)) {
            $this->afficherFormulaire($donnees + ['id' => $id], $erreurs);
            return;
        }

        $auteur->update($donnees, $id);

        Session::ajouterMessage('succes', $this->nomComplet($donnees) . ' a été modifié.');
        View::redirect('/auteurs');
    }

    /**
     * Supprime l'auteur.
     * Refusé si des livres lui sont liés, car un livre ne peut pas exister sans auteur.
     */
    public function delete(): void
    {
        $this->verifierJeton('/auteurs');

        $auteur = new Auteur();
        $selectId = $auteur->selectId($this->idRecu());

        if (!$selectId) {
            $this->auteurIntrouvable();
        }

        $nombreLivres = $auteur->compterLivres($selectId['id']);

        if ($nombreLivres > 0) {
            Session::ajouterMessage(
                'erreur',
                $this->nomComplet($selectId) . ' a encore ' . $nombreLivres
                . ' livre(s) au catalogue. Supprimez ou réattribuez ces livres d\'abord.'
            );
            View::redirect('/auteurs');
        }

        $auteur->delete($selectId['id']);

        Session::ajouterMessage('succes', $this->nomComplet($selectId) . ' a été supprimé.');
        View::redirect('/auteurs');
    }

    /* ------------------------------------------------------------
       Outils du contrôleur
      ------------------------------------------------------------ */

    /**
     * Règles de validation du formulaire d'un auteur.
     */
    private function valider(array $donnees): array
    {
        $validateur = new Validator($donnees);

        $validateur->champ('prenom', 'Le prénom')->requis()->max(80);
        $validateur->champ('nom', 'Le nom')->requis()->max(80);
        $validateur->champ('annee_naissance', 'L\'année de naissance')->entier()->entre(1000, (int) date('Y'));
        $validateur->champ('biographie', 'La biographie')->max(2000);

        return $validateur->erreurs();
    }

    private function nomComplet(array $auteur): string
    {
        return trim($auteur['prenom']) . ' ' . trim($auteur['nom']);
    }

    private function auteurIntrouvable(): never
    {
        Session::ajouterMessage('erreur', 'Cet auteur n\'existe pas ou a été supprimé.');
        View::redirect('/auteurs');
    }

    private function afficherFormulaire(array $auteur, array $erreurs = []): void
    {
        View::render('auteur/form', [
            'menu'     => 'auteurs',
            'auteur'   => $auteur,
            'erreurs'  => $erreurs,
            'anneeMax' => (int) date('Y'),
        ]);
    }
}

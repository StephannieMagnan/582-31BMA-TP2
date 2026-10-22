<?php

namespace App\Controllers;

use App\Models\Auteur;
use App\Models\Categorie;
use App\Models\Editeur;
use App\Models\Livre;
use App\Models\LivreCategorie;
use App\Providers\Session;
use App\Providers\Validator;
use App\Providers\View;

/**
 * Gestion des livres.
 */
class LivreController extends Controller
{
    /**
     * Ctalogue avec recherche et filtres.
     */
    public function index(): void
    {
        $filtres = [
            'texte'        => trim($_GET['texte'] ?? ''),
            'auteur_id'    => (int) ($_GET['auteur'] ?? 0),
            'categorie_id' => (int) ($_GET['categorie'] ?? 0),
        ];

        $livre = new Livre();
        $auteur = new Auteur();
        $categorie = new Categorie();

        View::render('livre/index', [
            'menu'        => 'livres',
            'livres'      => $livre->rechercher($filtres),
            'auteurs'     => $auteur->select('nom'),
            'categories'  => $categorie->select('nom'),
            'filtres'     => $filtres,
            'filtreActif' => $filtres['texte'] !== '' || $filtres['auteur_id'] > 0 || $filtres['categorie_id'] > 0,
        ]);
    }

    /**
     * Fiche complète d'un livre.
     */
    public function show(): void
    {
        $livre = new Livre();
        $selectId = $livre->selectAvecDetails($this->idRecu());

        if (!$selectId) {
            $this->livreIntrouvable();
        }

        $livreCategorie = new LivreCategorie();

        View::render('livre/show', [
            'menu'       => 'livres',
            'livre'      => $selectId,
            'categories' => $livreCategorie->categoriesDuLivre($selectId['id']),
        ]);
    }

    /**
     * Portrait de l'inventaire et livres à réapprovisionner.
     */
    public function inventaire(): void
    {
        $livre = new Livre();
        $auteur = new Auteur();

        View::render('livre/inventaire', [
            'menu'              => 'inventaire',
            'nombreLivres'      => $livre->compter(),
            'nombreExemplaires' => $livre->nombreExemplaires(),
            'nombreAuteurs'     => $auteur->compter(),
            'valeurInventaire'  => $livre->valeurInventaire(),
            'livresStockFaible' => $livre->selectStockFaible(),
            'livresRecents'     => $livre->selectRecents(6),
        ]);
    }

    /**
     * Formulaire d'ajout.
     */
    public function create(): void
    {
        $this->afficherFormulaire(['quantite_stock' => 0, 'categorie_ids' => []]);
    }

    /**
     * Valide puis ajoute le livre.
     */
    public function store(): void
    {
        $this->verifierJeton('/livre/create');

        $donnees = $this->donneesFormulaire();
        $erreurs = $this->valider($donnees);

        if (!empty($erreurs)) {
            $this->afficherFormulaire($donnees, $erreurs);
            return;
        }

        $livre = new Livre();
        $id = $livre->insert($this->preparer($donnees));

        $livreCategorie = new LivreCategorie();
        $livreCategorie->synchroniser($id, $donnees['categorie_ids'] ?? []);

        Session::ajouterMessage('succes', '« ' . trim($donnees['titre']) . ' » a été ajouté au catalogue.');
        View::redirect('/livre/show?id=' . $id);
    }

    /**
     * Formulaire de modification.
     */
    public function edit(): void
    {
        $livre = new Livre();
        $selectId = $livre->selectId($this->idRecu());

        if (!$selectId) {
            $this->livreIntrouvable();
        }

        $livreCategorie = new LivreCategorie();
        $selectId['categorie_ids'] = $livreCategorie->idsDuLivre($selectId['id']);

        $this->afficherFormulaire($selectId);
    }

    /**
     * Valide puis modifie le livre.
     */
    public function update(): void
    {
        $this->verifierJeton('/livres');

        $id = $this->idRecu();
        $livre = new Livre();

        if (!$livre->selectId($id)) {
            $this->livreIntrouvable();
        }

        $donnees = $this->donneesFormulaire();
        $erreurs = $this->valider($donnees);

        if (!empty($erreurs)) {
            $this->afficherFormulaire($donnees + ['id' => $id], $erreurs);
            return;
        }

        $livre->update($this->preparer($donnees), $id);

        $livreCategorie = new LivreCategorie();
        $livreCategorie->synchroniser($id, $donnees['categorie_ids'] ?? []);

        Session::ajouterMessage('succes', '« ' . trim($donnees['titre']) . ' » a été modifié.');
        View::redirect('/livre/show?id=' . $id);
    }

    /**
     * Supprime le livre.
     * Ses liens dans livre_categorie sont effacés par la base.
     */
    public function delete(): void
    {
        $this->verifierJeton('/livres');

        $livre = new Livre();
        $selectId = $livre->selectId($this->idRecu());

        if (!$selectId) {
            $this->livreIntrouvable();
        }

        $livre->delete($selectId['id']);

        Session::ajouterMessage('succes', '« ' . $selectId['titre'] . ' » a été retiré du catalogue.');
        View::redirect('/livres');
    }

    /* ------------------------------------------------------------
       Outils du contrôleur
      ------------------------------------------------------------ */

    /**
     * Règles de validation du formulaire d'un livre.
     */
    private function valider(array $donnees): array
    {
        $auteur = new Auteur();
        $editeur = new Editeur();
        $categorie = new Categorie();

        $validateur = new Validator($donnees);
        $anneeMaximale = (int) date('Y') + 1;

        $validateur->champ('titre', 'Le titre')->requis()->max(150);

        $validateur->champ('auteur_id', 'L\'auteur')
            ->requis('Choisissez un auteur dans la liste.')
            ->regle(fn ($id) => $auteur->selectId($id) !== false, 'Choisissez un auteur dans la liste.');

        $validateur->champ('editeur_id', 'L\'éditeur')
            ->regle(fn ($id) => $editeur->selectId($id) !== false, 'Choisissez un éditeur dans la liste.');

        $validateur->champ('annee_publication', 'L\'année')->entier()->entre(1450, $anneeMaximale);
        $validateur->champ('prix', 'Le prix')->requis()->nombre()->entre(0, 9999.99);
        $validateur->champ('quantite_stock', 'La quantité en stock')->requis()->entier()->entre(0, 99999);

        $validateur->champ('categorie_ids', 'Les catégories')
            ->regle(fn ($ids) => is_array($ids), 'Choisissez des catégories dans la liste.')
            ->regle(
                fn ($ids) => array_reduce($ids, fn ($valide, $id) => $valide && $categorie->selectId($id) !== false, true),
                'Une des catégories choisies n\'existe plus. Rechargez la page.'
            );

        $validateur->champ('resume', 'Le résumé')->max(5000);

        return $validateur->erreurs();
    }

    /**
     * Prépare les données validées pour la base.
     */
    private function preparer(array $donnees): array
    {
        $donnees['prix'] = str_replace(',', '.', trim($donnees['prix']));

        return $donnees;
    }

    /**
     * Renvoie au catalogue quand le livre demandé n'existe pas.
     */
    private function livreIntrouvable(): never
    {
        Session::ajouterMessage('erreur', 'Ce livre n\'existe pas ou a été supprimé.');
        View::redirect('/livres');
    }

    /**
     * Affiche le formulaire d'ajout ou de modification, avec les listes
     * nécessaires.
     */
    private function afficherFormulaire(array $livre, array $erreurs = []): void
    {
        $auteur = new Auteur();
        $editeur = new Editeur();
        $categorie = new Categorie();

        View::render('livre/form', [
            'menu'       => 'livres',
            'livre'      => $livre,
            'erreurs'    => $erreurs,
            'auteurs'    => $auteur->select('nom'),
            'editeurs'   => $editeur->select('nom'),
            'categories' => $categorie->select('nom'),
            'anneeMax'   => (int) date('Y') + 1,
        ]);
    }
}

<?php

namespace App\Models;

/**
 * Modèle de la table livre.
 *
 * Relations :
 *   - auteur_id
 *   - editeur_id
 *   - catégories (table livre_categorie)
 */
class Livre extends CRUD
{
    protected $table = 'livre';
    protected $primaryKey = 'id';
    protected $fillable = ['titre', 'auteur_id', 'editeur_id', 'annee_publication', 'prix', 'quantite_stock', 'resume'];

    /** À ce nombre d'exemplaires ou moins, le livre est à réapprovisionner. */
    public const SEUIL_STOCK_FAIBLE = 2;

    /* ------------------------------------------------------------
       Lecture avec les tables liées
      ------------------------------------------------------------ */

    /**
     * Un livre avec le nom de son auteur et de son éditeur, ou false.
     */
    public function selectAvecDetails($id)
    {
        $sql = 'SELECT l.*,
                       a.prenom AS auteur_prenom,
                       a.nom AS auteur_nom_famille,
                       CONCAT(a.prenom, \' \', a.nom) AS auteur_nom,
                       a.biographie AS auteur_biographie,
                       e.nom AS editeur_nom,
                       e.ville AS editeur_ville
                FROM livre l
                JOIN auteur a ON a.id = l.auteur_id
                LEFT JOIN editeur e ON e.id = l.editeur_id
                WHERE l.id = :id';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Recherche des livres avec le nom de l'auteur et de l'éditeur.
     */
    public function rechercher($filtres = [], $tri = 'l.titre', $limite = null)
    {
        $conditions = [];
        $parametres = [];

        if (!empty($filtres['texte'])) {
            $conditions[] = '(l.titre LIKE :texte_titre OR CONCAT(a.prenom, \' \', a.nom) LIKE :texte_auteur)';
            $parametres['texte_titre'] = '%' . $filtres['texte'] . '%';
            $parametres['texte_auteur'] = '%' . $filtres['texte'] . '%';
        }

        if (!empty($filtres['auteur_id'])) {
            $conditions[] = 'l.auteur_id = :auteur_id';
            $parametres['auteur_id'] = (int) $filtres['auteur_id'];
        }

        if (!empty($filtres['categorie_id'])) {
            $conditions[] = 'l.id IN (SELECT livre_id FROM livre_categorie WHERE categorie_id = :categorie_id)';
            $parametres['categorie_id'] = (int) $filtres['categorie_id'];
        }

        if (isset($filtres['stock_max'])) {
            $conditions[] = 'l.quantite_stock <= :stock_max';
            $parametres['stock_max'] = (int) $filtres['stock_max'];
        }

        // Seuls ces tris sont permis.
        $trisPermis = ['l.titre', 'l.id DESC', 'l.quantite_stock'];
        if (!in_array($tri, $trisPermis, true)) {
            $tri = 'l.titre';
        }

        $sql = 'SELECT l.*,
                       CONCAT(a.prenom, \' \', a.nom) AS auteur_nom,
                       e.nom AS editeur_nom
                FROM livre l
                JOIN auteur a ON a.id = l.auteur_id
                LEFT JOIN editeur e ON e.id = l.editeur_id';

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY ' . $tri;

        if ($limite !== null) {
            $sql .= ' LIMIT ' . (int) $limite;
        }

        $stmt = $this->prepare($sql);
        $stmt->execute($parametres);

        return $stmt->fetchAll();
    }

    /** Les derniers livres ajoutés. */
    public function selectRecents($nombre = 6)
    {
        return $this->rechercher([], 'l.id DESC', $nombre);
    }

    /** Les livres dont le stock est faible. */
    public function selectStockFaible()
    {
        return $this->rechercher(['stock_max' => self::SEUIL_STOCK_FAIBLE], 'l.quantite_stock');
    }

    /* ------------------------------------------------------------
       Statistiques
      ------------------------------------------------------------ */

    /** Valeur totale de l'inventaire : somme de prix × quantité. */
    public function valeurInventaire()
    {
        return (float) $this->query('SELECT COALESCE(SUM(prix * quantite_stock), 0) FROM livre')->fetchColumn();
    }

    /** Nombre total d'exemplaires en stock. */
    public function nombreExemplaires()
    {
        return (int) $this->query('SELECT COALESCE(SUM(quantite_stock), 0) FROM livre')->fetchColumn();
    }
}

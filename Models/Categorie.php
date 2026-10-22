<?php

namespace App\Models;

/**
 * Modèle de la table categorie.
 */
class Categorie extends CRUD
{
    protected $table = 'categorie';
    protected $primaryKey = 'id';
    protected $fillable = ['nom', 'description'];

    /**
     * Catégories triées par nom, avec leur nombre de livres.
     */
    public function selectAvecNombreLivres()
    {
        $sql = 'SELECT c.*, COUNT(lc.livre_id) AS nombre_livres
                FROM categorie c
                LEFT JOIN livre_categorie lc ON lc.categorie_id = c.id
                GROUP BY c.id
                ORDER BY c.nom';

        return $this->query($sql)->fetchAll();
    }

    /**
     * Vérifie si une autre catégorie porte déjà ce nom.
     */
    public function nomExiste($nom, $idExclu = 0)
    {
        $stmt = $this->prepare('SELECT COUNT(*) FROM categorie WHERE nom = :nom AND id <> :id');
        $stmt->bindValue(':nom', $nom);
        $stmt->bindValue(':id', $idExclu);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }
}

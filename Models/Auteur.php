<?php

namespace App\Models;

/**
 * Modèle de la table auteur.
 * Relation un-à-plusieurs.
 */
class Auteur extends CRUD
{
    protected $table = 'auteur';
    protected $primaryKey = 'id';
    protected $fillable = ['prenom', 'nom', 'annee_naissance', 'biographie'];

    /**
     * Auteurs triés par nom, avec leur nombre de livres.
     */
    public function selectAvecNombreLivres()
    {
        $sql = 'SELECT a.*, COUNT(l.id) AS nombre_livres
                FROM auteur a
                LEFT JOIN livre l ON l.auteur_id = a.id
                GROUP BY a.id
                ORDER BY a.nom, a.prenom';

        return $this->query($sql)->fetchAll();
    }

    /**
     * Nombre de livres écrits par un auteur.
     */
    public function compterLivres($id)
    {
        $stmt = $this->prepare('SELECT COUNT(*) FROM livre WHERE auteur_id = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}

<?php

namespace App\Models;

/**
 * Modèle de la table editeur.
 * Relation un-à-plusieurs.
 */
class Editeur extends CRUD
{
    protected $table = 'editeur';
    protected $primaryKey = 'id';
    protected $fillable = ['nom', 'ville', 'site_web'];

    /**
     * Éditeurs triés par nom, avec leur nombre de livres.
     */
    public function selectAvecNombreLivres()
    {
        $sql = 'SELECT e.*, COUNT(l.id) AS nombre_livres
                FROM editeur e
                LEFT JOIN livre l ON l.editeur_id = e.id
                GROUP BY e.id
                ORDER BY e.nom';

        return $this->query($sql)->fetchAll();
    }
}

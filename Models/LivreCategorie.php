<?php

namespace App\Models;

use PDOException;

/**
 * Modèle de la table de liaison livre_categorie.
 * Relation plusieurs-à-plusieurs.
 */
class LivreCategorie extends CRUD
{
    protected $table = 'livre_categorie';
    protected $primaryKey = 'livre_id';
    protected $fillable = ['livre_id', 'categorie_id'];

    /**
     * Catégories d'un livre, triées par nom.
     */
    public function categoriesDuLivre($livreId)
    {
        $sql = 'SELECT c.*
                FROM categorie c
                JOIN livre_categorie lc ON lc.categorie_id = c.id
                WHERE lc.livre_id = :livre_id
                ORDER BY c.nom';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(':livre_id', $livreId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Identifiants des catégories d'un livre.
     */
    public function idsDuLivre($livreId)
    {
        return array_column($this->categoriesDuLivre($livreId), 'id');
    }

    /**
     * Remplace les catégories d'un livre par celles reçues.
     */
    public function synchroniser($livreId, $categorieIds)
    {
        $this->beginTransaction();

        try {
            // La clé primaire est livre_id : delete() retire tous les liens du livre
            $this->delete($livreId);

            foreach (array_unique($categorieIds) as $categorieId) {
                $this->insert(['livre_id' => $livreId, 'categorie_id' => $categorieId]);
            }

            $this->commit();
        } catch (PDOException $exception) {
            $this->rollBack();
            throw $exception;
        }
    }
}

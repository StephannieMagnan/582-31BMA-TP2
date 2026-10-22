<?php

namespace App\Models;

use PDO;

/**
 * Classe CRUD généralisée.
 *
 * Chaque modèle enfant indique seulement :
 *   - le nom de sa table;
 *   - sa clé primaire;
 *   - les colonnes qu'on peut enregistrer.
 */
abstract class CRUD extends PDO
{
    /** Nom. */
    protected $table = '';

    /** Clé primaire. */
    protected $primaryKey = 'id';

    /** Colonnes. */
    protected $fillable = [];

    /**
     * Ouvre la connexion à la base de données.
     */
    public function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOTE . ';dbname=' . DB_NOM . ';charset=utf8mb4';

        parent::__construct($dsn, DB_UTILISATEUR, DB_MOT_DE_PASSE, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // erreurs SQL = exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // résultats en tableaux associatifs
            PDO::ATTR_EMULATE_PREPARES   => false,                  // vraies requêtes préparées
        ]);
    }

    /* ------------------------------------------------------------
       Lecture
      ------------------------------------------------------------ */

    /**
     * Retourne tous les enregistrements de la table.
     */
    public function select($field = null, $order = 'ASC')
    {
        $field = $field ?? $this->primaryKey;
        $order = strtoupper($order) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM $this->table ORDER BY $field $order";
        $stmt = $this->query($sql);

        return $stmt->fetchAll();
    }

    /**
     * Retourne l'enregistrement correspondant à la valeur, ou false.
     */
    public function selectId($value)
    {
        $sql = "SELECT * FROM $this->table WHERE $this->primaryKey = :$this->primaryKey";

        $stmt = $this->prepare($sql);
        $stmt->bindValue(":$this->primaryKey", $value);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            return $stmt->fetch();
        }

        return false;
    }

    /**
     * Retourne le nombre d'enregistrements de la table.
     */
    public function compter()
    {
        return (int) $this->query("SELECT COUNT(*) FROM $this->table")->fetchColumn();
    }

    /* ------------------------------------------------------------
       Écriture
      ------------------------------------------------------------ */

    /**
     * Ajoute un enregistrement et retourne son identifiant.
     */
    public function insert($data)
    {
        $data = $this->filtrer($data);

        $fieldName = implode(', ', array_keys($data));
        $fieldValue = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO $this->table ($fieldName) VALUES ($fieldValue)";
        $stmt = $this->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();

        return $this->lastInsertId();
    }

    /**
     * Modifie l'enregistrement dont la clé primaire vaut $id.
     */
    public function update($data, $id)
    {
        $data = $this->filtrer($data);

        $fieldName = null;
        foreach ($data as $key => $value) {
            $fieldName .= "$key = :$key, ";
        }
        $fieldName = rtrim($fieldName, ', ');

        $sql = "UPDATE $this->table SET $fieldName WHERE $this->primaryKey = :cle_primaire";
        $stmt = $this->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':cle_primaire', $id);

        return $stmt->execute();
    }

    /**
     * Supprime l'enregistrement dont la clé primaire vaut $value.
     */
    public function delete($value)
    {
        $sql = "DELETE FROM $this->table WHERE $this->primaryKey = :$this->primaryKey";

        $stmt = $this->prepare($sql);
        $stmt->bindValue(":$this->primaryKey", $value);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /* ------------------------------------------------------------
       Outils
      ------------------------------------------------------------ */

    /**
     * Garde seulement les colonnes permises.
     * Un champ vide devient NULL dans la base.
     */
    private function filtrer($data)
    {
        $data = array_intersect_key($data, array_flip($this->fillable));

        return array_map(function ($value) {
            $value = is_string($value) ? trim($value) : $value;

            return $value === '' ? null : $value;
        }, $data);
    }
}

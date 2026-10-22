<?php

namespace App\Providers;

/**
 * Validation des données d'un formulaire avant leur enregistrement.
 *
 * Les règles s'enchaînent pour chaque champ.
 *
 * Chaque champ garde seulement sa première erreur.
 */
class Validator
{
    /** Données reçues du formulaire. */
    private array $donnees;

    /** Erreurs trouvées, une par champ */
    private array $erreurs = [];

    /* Champ en cours de validation */
    private string $champ = '';
    private string $libelle = '';
    private mixed $valeur = null;

    public function __construct(array $donnees)
    {
        $this->donnees = $donnees;
    }

    /**
     * Choisit le champ à valider.
     */
    public function champ(string $nom, string $libelle): self
    {
        $this->champ = $nom;
        $this->libelle = $libelle;

        $valeur = $this->donnees[$nom] ?? '';
        $this->valeur = is_string($valeur) ? trim($valeur) : $valeur;

        return $this;
    }

    /* ------------------------------------------------------------
       Règles
      ------------------------------------------------------------ */

    /**
     * Le champ doit être rempli.
     */
    public function requis(?string $message = null): self
    {
        if ($this->peutVerifier(false) && $this->estVide()) {
            $this->ajouterErreur($message ?? $this->libelle . ' est obligatoire.');
        }

        return $this;
    }

    /**
     * Le texte doit contenir au plus $longueur caractères.
     */
    public function max(int $longueur): self
    {
        if ($this->peutVerifier() && mb_strlen((string) $this->valeur) > $longueur) {
            $this->ajouterErreur($this->libelle . " doit contenir $longueur caractères ou moins.");
        }

        return $this;
    }

    /**
     * La valeur doit être un nombre entier (ex. : 1897).
     */
    public function entier(): self
    {
        if ($this->peutVerifier() && filter_var($this->valeur, FILTER_VALIDATE_INT) === false) {
            $this->ajouterErreur($this->libelle . ' doit être un nombre entier.');
        }

        return $this;
    }

    /**
     * La valeur doit être un nombre, avec un point ou une virgule.
     */
    public function nombre(): self
    {
        if ($this->peutVerifier() && !is_numeric(str_replace(',', '.', (string) $this->valeur))) {
            $this->ajouterErreur($this->libelle . ' doit être un nombre, par exemple 12,95.');
        }

        return $this;
    }

    /**
     * La valeur numérique doit être comprise entre $minimum et $maximum.
     */
    public function entre(float $minimum, float $maximum): self
    {
        if (!$this->peutVerifier()) {
            return $this;
        }

        $nombre = (float) str_replace(',', '.', (string) $this->valeur);

        if ($nombre < $minimum || $nombre > $maximum) {
            $this->ajouterErreur(
                $this->libelle . ' doit être entre ' . self::formaterNombre($minimum)
                . ' et ' . self::formaterNombre($maximum) . '.'
            );
        }

        return $this;
    }

    /**
     * La valeur doit être une adresse Web complète.
     */
    public function url(): self
    {
        if ($this->peutVerifier() && filter_var($this->valeur, FILTER_VALIDATE_URL) === false) {
            $this->ajouterErreur($this->libelle . ' doit être une adresse complète, par exemple https://www.exemple.com.');
        }

        return $this;
    }

    /**
     * Règle propre au projet : $test reçoit la valeur et retourne true si elle est valide.
     */
    public function regle(callable $test, string $message): self
    {
        if ($this->peutVerifier() && !$test($this->valeur)) {
            $this->ajouterErreur($message);
        }

        return $this;
    }

    /* ------------------------------------------------------------
       Résultat
      ------------------------------------------------------------ */

    public function erreurs(): array
    {
        return $this->erreurs;
    }

    public function estValide(): bool
    {
        return empty($this->erreurs);
    }

    /* ------------------------------------------------------------
       Outils internes
      ------------------------------------------------------------ */

    /**
     * Une règle s'applique seulement si le champ n'a pas déjà une erreur.
     */
    private function peutVerifier(bool $ignorerSiVide = true): bool
    {
        if (isset($this->erreurs[$this->champ])) {
            return false;
        }

        return !($ignorerSiVide && $this->estVide());
    }

    private function estVide(): bool
    {
        return $this->valeur === null || $this->valeur === '' || $this->valeur === [];
    }

    private function ajouterErreur(string $message): void
    {
        $this->erreurs[$this->champ] = $message;
    }

    private static function formaterNombre(float $nombre): string
    {
        if ($nombre == (int) $nombre) {
            return (string) (int) $nombre;
        }

        return number_format($nombre, 2, ',', "\u{00A0}");
    }
}

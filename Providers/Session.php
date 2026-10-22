<?php

namespace App\Providers;

/**
 * Données gardées en session : messages de confirmation ou d'erreur.
 */
class Session
{
    /**
     * Garde un message pour l'afficher sur la page suivante.
     */
    public static function ajouterMessage(string $type, string $texte): void
    {
        $_SESSION['messages'][] = ['type' => $type, 'texte' => $texte];
    }

    /**
     * Retourne les messages en attente et les retire de la session.
     */
    public static function lireMessages(): array
    {
        $messages = $_SESSION['messages'] ?? [];
        unset($_SESSION['messages']);

        return $messages;
    }

    /**
     * Retourne le jeton secret de la session.
     */
    public static function jetonCsrf(): string
    {
        if (empty($_SESSION['jeton_csrf'])) {
            $_SESSION['jeton_csrf'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['jeton_csrf'];
    }

    /**
     * Vérifie que le formulaire reçu provient bien du site.
     */
    public static function jetonCsrfValide(): bool
    {
        return hash_equals(self::jetonCsrf(), (string) ($_POST['jeton_csrf'] ?? ''));
    }
}

<?php

namespace App\Routes;

use App\Providers\View;

/**
 * Routeur de l'application.
 */
class Route
{
    /** Routes enregistrées. */
    private static array $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    /**
     * Enregistre une route qui répond à une requête GET..
     */
    public static function get(string $adresse, string $action): void
    {
        self::$routes['GET'][$adresse] = $action;
    }

    /**
     * Enregistre une route qui répond à une requête POST.
     */
    public static function post(string $adresse, string $action): void
    {
        self::$routes['POST'][$adresse] = $action;
    }

    /**
     * Trouve la route qui correspond à la requête et appelle l'action du contrôleur.
     */
    public static function dispatch(): void
    {
        $methode = $_SERVER['REQUEST_METHOD'];
        $adresse = self::adresseDemandee();

        if (!isset(self::$routes[$methode][$adresse])) {
            self::pageIntrouvable();
            return;
        }

        [$nomControleur, $action] = explode('@', self::$routes[$methode][$adresse]);
        $classe = 'App\\Controllers\\' . $nomControleur;

        $controleur = new $classe();
        $controleur->$action();
    }

    /**
     * Retourne l'adresse demandée.
     */
    private static function adresseDemandee(): string
    {
        $chemin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $chemin = rawurldecode($chemin);

        if (BASE !== '' && str_starts_with($chemin, BASE)) {
            $chemin = substr($chemin, strlen(BASE));
        }

        // /livres/ et /livres mènent à la même route
        $chemin = '/' . trim($chemin, '/');

        return $chemin === '/index.php' ? '/' : $chemin;
    }

    /**
     * Affiche la page 404 quand aucune route ne correspond.
     */
    private static function pageIntrouvable(): void
    {
        http_response_code(404);

        View::render('erreur', [
            'titre'   => 'Page introuvable',
            'message' => 'L\'adresse demandée ne correspond à aucune page de Nemesis.',
        ]);
    }
}

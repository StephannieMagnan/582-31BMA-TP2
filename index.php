<?php

/**
 * Point d'entrée unique de l'application.
 *
 * Grâce au fichier .htaccess, toutes les requêtes passent par ce fichier,
 * qui charge la configuration, puis transmet l'adresse demandée au routeur.
 */

use App\Providers\View;
use App\Routes\Route;

session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Chemin de base du site (ex. : /librairie-tp2 sur N0C, vide en local
 * si le projet est à la racine). Sert à construire toutes les adresses.
 */
define('BASE', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

/**
 * Toute exception non attrapée affiche la page d'erreur au lieu d'une page blanche.
 */
set_exception_handler(function (Throwable $exception): void {
    error_log($exception->getMessage());
    http_response_code(500);

    View::render('erreur', [
        'titre'   => 'La page n\'a pas pu être chargée',
        'message' => 'Vérifiez la connexion à la base de données dans config/config.php, puis réessayez.',
        'detail'  => MODE_DEBUG ? $exception->getMessage() : null,
    ]);
});

require_once __DIR__ . '/Routes/web.php';

Route::dispatch();

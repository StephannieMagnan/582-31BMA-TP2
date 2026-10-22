<?php

namespace App\Providers;

use App\Models\Livre;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

/**
 * Affichage des vues avec Twig.
 */
class View
{
    /** Instance de Twig. */
    private static ?Environment $twig = null;

    /**
     * Affiche une vue du dossier views.
     */
    public static function render(string $gabarit, array $donnees = []): void
    {
        echo self::twig()->render($gabarit . '.php.twig', $donnees);
    }

    /**
     * Redirige vers une autre route du site et arrête le script.
     */
    public static function redirect(string $adresse): never
    {
        header('Location: ' . BASE . $adresse);
        exit;
    }

    /**
     * Configure Twig : dossier des vues, variables globales et filtres.
     */
    private static function twig(): Environment
    {
        if (self::$twig === null) {
            $chargeur = new FilesystemLoader(__DIR__ . '/../views');

            self::$twig = new Environment($chargeur, [
                'debug' => MODE_DEBUG,
                'cache' => false,
            ]);

            // Variables accessibles dans toutes les vues
            self::$twig->addGlobal('base', BASE);
            self::$twig->addGlobal('nom_site', NOM_SITE);
            self::$twig->addGlobal('jeton_csrf', Session::jetonCsrf());
            self::$twig->addGlobal('annee', date('Y'));
            self::$twig->addGlobal('seuil_stock', Livre::SEUIL_STOCK_FAIBLE);

            // {{ livre.prix|prix }} affiche 12,95 $
            self::$twig->addFilter(new TwigFilter('prix', [self::class, 'formaterPrix']));
        }

        // Les messages sont lus au dernier moment, puis retirés de la session
        self::$twig->addGlobal('messages', Session::lireMessages());

        return self::$twig;
    }

    /**
     * Formate un prix : 12,95 $
     */
    public static function formaterPrix(float $prix): string
    {
        return number_format($prix, 2, ',', "\u{00A0}") . "\u{00A0}$";
    }
}

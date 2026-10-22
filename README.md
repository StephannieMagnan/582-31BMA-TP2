# Nemesis : librairie de l'étrange (TP2, MVC)

Projet du cours 582-31VB-MA, Programmation Web avancée, Collège de Maisonneuve.
Migration du TP1 vers l'architecture MVC, avec Twig pour les vues.

## Structure

| Dossier        | Rôle                                                                 |
|----------------|----------------------------------------------------------------------|
| `Models/`      | Modèles : une classe par table et l'accès à la base (PDO)            |
| `Controllers/` | Contrôleurs : méthodes d'action appelées par les routes              |
| `views/`       | Vues Twig (`.php.twig`)                                              |
| `Routes/`      | Routeur (`Route.php`) et liste des routes (`web.php`)                |
| `Providers/`   | Services : affichage Twig, session et messages, validation           |
| `config/`      | Configuration de la base de données                                  |
| `css/`, `js/`  | Feuille de style et scripts                                          |

## Installation

1. Importer `librairie.sql` dans MySQL.
2. Copier `config/config.exemple.php` sous le nom `config/config.php` et entrer les accès à la base.
3. Installer les dépendances : `composer install` (crée le dossier `vendor/`).
4. Ouvrir le site dans un serveur Apache (le fichier `.htaccess` envoie toutes les requêtes à `index.php`).

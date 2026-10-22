<?php

namespace App\Controllers;

use App\Providers\View;

/**
 * Page d'accueil : vue statique de présentation de la librairie.
 */
class AccueilController extends Controller
{
    public function index(): void
    {
        View::render('accueil', ['menu' => 'accueil']);
    }
}

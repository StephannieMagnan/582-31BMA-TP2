<?php

/**
 * Liste des routes de l'application.
 */

use App\Routes\Route;

// Accueil 
Route::get('/', 'AccueilController@index');

// Livres 
Route::get('/livres', 'LivreController@index');
Route::get('/livres/inventaire', 'LivreController@inventaire');
Route::get('/livre/show', 'LivreController@show');
Route::get('/livre/create', 'LivreController@create');
Route::post('/livre/store', 'LivreController@store');
Route::get('/livre/edit', 'LivreController@edit');
Route::post('/livre/update', 'LivreController@update');
Route::post('/livre/delete', 'LivreController@delete');

// Auteurs
Route::get('/auteurs', 'AuteurController@index');
Route::get('/auteur/create', 'AuteurController@create');
Route::post('/auteur/store', 'AuteurController@store');
Route::get('/auteur/edit', 'AuteurController@edit');
Route::post('/auteur/update', 'AuteurController@update');
Route::post('/auteur/delete', 'AuteurController@delete');

// Éditeurs
Route::get('/editeurs', 'EditeurController@index');
Route::get('/editeur/create', 'EditeurController@create');
Route::post('/editeur/store', 'EditeurController@store');
Route::get('/editeur/edit', 'EditeurController@edit');
Route::post('/editeur/update', 'EditeurController@update');
Route::post('/editeur/delete', 'EditeurController@delete');

// Catégories
Route::get('/categories', 'CategorieController@index');
Route::get('/categorie/create', 'CategorieController@create');
Route::post('/categorie/store', 'CategorieController@store');
Route::get('/categorie/edit', 'CategorieController@edit');
Route::post('/categorie/update', 'CategorieController@update');
Route::post('/categorie/delete', 'CategorieController@delete');

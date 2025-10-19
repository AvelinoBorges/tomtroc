<?php
/**
 * Point d'entrée de l'application TomTroc
 * Plateforme d'échange de livres entre particuliers
 */

// Affichage des erreurs en mode développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrage de la session
session_start();

// Définition des constantes
define('ROOT', dirname(__FILE__));
define('DS', DIRECTORY_SEPARATOR);

// Chargement de l'autoloader
require_once ROOT . DS . 'config' . DS . 'autoloader.php';

// Chargement du routeur
require_once ROOT . DS . 'config' . DS . 'routes.php';

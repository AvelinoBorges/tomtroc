<?php
/**
 * Point d'entrée principal de l'application TomTroc
 * 
 * Ce fichier est le Front Controller de l'application. Toutes les requêtes HTTP
 * passent par ce fichier qui initialise l'environnement et délègue le traitement
 * au système de routing.
 * 
 * Fonctionnalités:
 * - Configuration du niveau de rapport d'erreurs (mode développement)
 * - Initialisation de la session PHP pour la gestion de l'authentification
 * - Définition des constantes globales (ROOT, DS)
 * - Chargement automatique des classes (autoloader PSR-4)
 * - Démarrage du système de routing pour dispatcher les requêtes
 * 
 * Architecture:
 * - Pattern Front Controller: Un seul point d'entrée pour toute l'application
 * - Pattern MVC: Model-View-Controller via le routing
 * - Autoloading: Chargement automatique des classes sans require manuels
 * 
 * Constantes définies:
 * @const ROOT - Chemin absolu vers la racine du projet
 * @const DS - Séparateur de répertoires selon l'OS (/ ou \)
 * 
 * @package TomTroc
 * @author AvelinoBorges
 * @version 1.0
 */

// ========================================
// Configuration de l'environnement PHP
// ========================================

// Affichage des erreurs en mode développement
// En production, ces valeurs devraient être à 0 pour ne pas exposer d'informations sensibles
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ========================================
// Initialisation de la session
// ========================================

// Démarrage de la session PHP pour gérer:
// - L'authentification de l'utilisateur ($_SESSION['user'])
// - Les messages flash (succès, erreurs)
// - Le panier ou les préférences temporaires
session_start();

// ========================================
// Définition des constantes globales
// ========================================

// ROOT: Chemin absolu vers le répertoire racine du projet
// Utilisé pour construire tous les chemins de fichiers de manière portable
define('ROOT', dirname(__FILE__));

// DS: Directory Separator - Séparateur de répertoires selon l'OS
// Windows utilise '\', Linux/Mac utilisent '/'
// Cette constante permet d'écrire du code portable
define('DS', DIRECTORY_SEPARATOR);

// ========================================
// Chargement des composants de l'application
// ========================================

// Chargement de l'autoloader PSR-4
// Permet le chargement automatique des classes (Controllers, Models, Services)
// sans avoir à écrire des require_once pour chaque classe
require_once ROOT . DS . 'config' . DS . 'autoloader.php';

// Chargement du système de routing
// Parse l'URL, détermine le controller et l'action à exécuter,
// puis instancie le controller approprié et appelle la méthode correspondante
require_once ROOT . DS . 'config' . DS . 'routes.php';

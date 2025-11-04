<?php
/**
 * Système de routage (Router) de l'application TomTroc
 * 
 * Ce fichier implémente le pattern Front Controller pour gérer le routage
 * des requêtes HTTP vers les contrôleurs et actions appropriés.
 * 
 * Fonctionnement du routage:
 * 1. Parse l'URL demandée par l'utilisateur
 * 2. Extrait le contrôleur, l'action et les paramètres
 * 3. Instancie le contrôleur correspondant
 * 4. Appelle la méthode (action) avec les paramètres
 * 5. Gère les erreurs 404 si contrôleur/action inexistant
 * 
 * Format des URLs:
 * - /tomtroc/ → HomeController::index()
 * - /tomtroc/auth/login → AuthController::login()
 * - /tomtroc/book/show/5 → BookController::show(5)
 * - /tomtroc/user/profile/10 → UserController::profile(10)
 * 
 * Convention de nommage:
 * - URL: /books → Contrôleur: BooksController
 * - URL: /auth → Contrôleur: AuthController
 * - Premier segment = contrôleur, deuxième segment = action
 * 
 * Architecture MVC:
 * Ce routeur est le point d'entrée du pattern MVC. Il fait le lien entre
 * la requête HTTP et le contrôleur qui va traiter la logique métier.
 * 
 * @package TomTroc\Config
 * @author TomTroc
 * @version 1.0
 */

// ========================================
// Étape 1: Récupération et nettoyage de l'URL
// ========================================

// Récupération de l'URI complète de la requête (ex: /tomtroc/auth/login?user=123)
$requestUri = $_SERVER['REQUEST_URI'];

// Récupération du chemin du script (ex: /tomtroc)
// Permet de gérer l'application dans un sous-dossier
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// Nettoyage: Retirer le chemin du script de l'URI
// Ex: /tomtroc/auth/login → auth/login
$uri = str_replace($scriptName, '', $requestUri);

// Supprimer les slashes au début et à la fin
// Ex: /auth/login/ → auth/login
$uri = trim($uri, '/');

// Supprimer la query string (paramètres GET après le ?)
// Ex: auth/login?redirect=home → auth/login
// Les paramètres GET restent accessibles via $_GET
if (strpos($uri, '?') !== false) {
    $uri = substr($uri, 0, strpos($uri, '?'));
}

// ========================================
// Étape 2: Parsing de l'URL en segments
// ========================================

// Séparation de l'URL en segments via le séparateur /
// Ex: "auth/login" → ['auth', 'login']
// Ex: "book/show/5" → ['book', 'show', '5']
// Ex: "" (page d'accueil) → []
$segments = !empty($uri) ? explode('/', $uri) : [];

// ========================================
// Étape 3: Détermination du contrôleur et de l'action
// ========================================

// Déterminer le nom du contrôleur depuis le premier segment
// Si absent, utiliser HomeController par défaut (page d'accueil)
// Ajoute 'Controller' au nom et met la première lettre en majuscule
// Ex: 'auth' → 'AuthController', 'books' → 'BooksController'
$controllerName = !empty($segments[0]) ? ucfirst($segments[0]) . 'Controller' : 'HomeController';

// Déterminer le nom de l'action (méthode) depuis le deuxième segment
// Si absent, utiliser 'index' par défaut
// Ex: 'login' → login(), 'show' → show()
$action = !empty($segments[1]) ? $segments[1] : 'index';

// Extraire les paramètres supplémentaires (segments à partir du 3ème)
// Ces paramètres seront passés à la méthode du contrôleur
// Ex: book/show/5 → $params = [5]
// Ex: user/profile/10 → $params = [10]
$params = array_slice($segments, 2);

// ========================================
// Étape 4: Instanciation et exécution du contrôleur
// ========================================

// Construire le chemin complet vers le fichier du contrôleur
$controllerFile = ROOT . DS . 'src' . DS . 'controllers' . DS . $controllerName . '.php';

// Vérifier si le fichier du contrôleur existe
if (file_exists($controllerFile)) {
    // Instancier le contrôleur
    // L'autoloader a déjà chargé la classe, on peut l'instancier directement
    $controller = new $controllerName();
    
    // Vérifier si la méthode (action) existe dans le contrôleur
    if (method_exists($controller, $action)) {
        // Appeler la méthode avec les paramètres extraits de l'URL
        // call_user_func_array permet de passer un tableau de paramètres
        // Ex: $controller->show(5) pour BookController
        call_user_func_array([$controller, $action], $params);
    } else {
        // Action non trouvée dans le contrôleur
        // Retourner une erreur HTTP 404 avec message explicite
        http_response_code(404);
        echo "Erreur 404 : Action non trouvée";
    }
} else {
    // Contrôleur non trouvé dans le système de fichiers
    // Retourner une erreur HTTP 404 avec message explicite
    http_response_code(404);
    echo "Erreur 404 : Page non trouvée";
}


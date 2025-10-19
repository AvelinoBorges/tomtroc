<?php
/**
 * Configuration du routage de l'application
 */

// Récupération de l'URL demandée
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// Nettoyage de l'URL
$uri = str_replace($scriptName, '', $requestUri);
$uri = trim($uri, '/');

// Remover query string da URI (parâmetros GET)
if (strpos($uri, '?') !== false) {
    $uri = substr($uri, 0, strpos($uri, '?'));
}

// Séparation de l'URL en segments
$segments = !empty($uri) ? explode('/', $uri) : [];

// Détermination du contrôleur et de l'action
$controllerName = !empty($segments[0]) ? ucfirst($segments[0]) . 'Controller' : 'HomeController';
$action = !empty($segments[1]) ? $segments[1] : 'index';

// Paramètres supplémentaires
$params = array_slice($segments, 2);

// Vérification de l'existence du contrôleur
$controllerFile = ROOT . DS . 'src' . DS . 'controllers' . DS . $controllerName . '.php';

if (file_exists($controllerFile)) {
    $controller = new $controllerName();
    
    // Vérification de l'existence de l'action
    if (method_exists($controller, $action)) {
        call_user_func_array([$controller, $action], $params);
    } else {
        // Action non trouvée
        http_response_code(404);
        echo "Erreur 404 : Action non trouvée";
    }
} else {
    // Contrôleur non trouvé
    http_response_code(404);
    echo "Erreur 404 : Page non trouvée";
}


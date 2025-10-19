<?php
/**
 * Contrôleur de base
 * Tous les contrôleurs héritent de cette classe
 */

abstract class Controller
{
    /**
     * Affiche une vue
     * 
     * @param string $viewName Nom de la vue
     * @param array $data Données à passer à la vue
     * @return void
     */
    protected function render(string $viewName, array $data = []): void
    {
        // Extraction des données pour les rendre accessibles dans la vue
        extract($data);
        
        // Chemin vers le fichier de vue
        $viewFile = ROOT . DS . 'src' . DS . 'views' . DS . $viewName . '.php';
        
        // Vérification de l'existence de la vue
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new Exception("La vue {$viewName} n'existe pas");
        }
    }
    
    /**
     * Redirige vers une URL
     * 
     * @param string $url URL de redirection
     * @return void
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
}

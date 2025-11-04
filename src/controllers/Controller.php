<?php
/**
 * Contrôleur de base abstrait
 * 
 * Classe parent dont héritent tous les contrôleurs de l'application.
 * Fournit les fonctionnalités communes pour le rendu des vues et la redirection.
 * Cette classe ne peut pas être instanciée directement (abstract).
 * 
 * @abstract
 */
abstract class Controller
{
    /**
     * Affiche une vue en incluant le fichier correspondant
     * 
     * Méthode principale pour le rendu des vues. Elle extrait les données du tableau
     * pour les rendre accessibles en tant que variables dans la vue, construit le chemin
     * vers le fichier de vue, vérifie son existence et l'inclut.
     * Lance une exception si la vue demandée n'existe pas.
     * 
     * @param string $viewName Nom de la vue (chemin relatif sans extension, ex: 'auth/login')
     * @param array $data Tableau associatif de données à transmettre à la vue
     * @return void
     * @throws Exception Si le fichier de vue n'existe pas
     */
    protected function render(string $viewName, array $data = []): void
    {
        // Extraire les données du tableau pour créer des variables individuelles
        // Exemple: ['user' => $user] devient la variable $user dans la vue
        extract($data);
        
        // Construire le chemin complet vers le fichier de vue
        // Utilise les constantes ROOT et DS définies dans l'application
        $viewFile = ROOT . DS . 'src' . DS . 'views' . DS . $viewName . '.php';
        
        // Vérifier que le fichier de vue existe avant de l'inclure
        if (file_exists($viewFile)) {
            // Inclure le fichier de vue (exécuté une seule fois)
            require_once $viewFile;
        } else {
            // Lever une exception si la vue demandée est introuvable
            throw new Exception("La vue {$viewName} n'existe pas");
        }
    }
    
    /**
     * Redirige l'utilisateur vers une URL spécifique
     * 
     * Envoie un en-tête HTTP de redirection (Location) et termine
     * immédiatement l'exécution du script. Utilisée pour rediriger
     * après des actions (connexion, déconnexion, soumission de formulaire, etc.).
     * 
     * @param string $url L'URL de destination (peut être relative ou absolue)
     * @return void
     */
    protected function redirect(string $url): void
    {
        // Envoyer l'en-tête HTTP de redirection
        header("Location: {$url}");
        // Terminer l'exécution pour garantir que la redirection se produit
        exit;
    }
}

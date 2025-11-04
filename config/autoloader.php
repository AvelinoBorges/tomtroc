<?php
/**
 * Autoloader PSR-4 pour le chargement automatique des classes
 * 
 * Ce fichier configure le chargement automatique (autoloading) des classes PHP
 * de l'application TomTroc. Il implémente une version simplifiée du standard PSR-4
 * qui permet d'instancier des classes sans avoir à écrire des require_once manuels.
 * 
 * Fonctionnement:
 * - Lorsqu'une classe est instanciée (ex: new User()), PHP appelle automatiquement
 *   la fonction enregistrée via spl_autoload_register()
 * - La fonction recherche le fichier correspondant dans les répertoires définis
 * - Si trouvé, le fichier est chargé automatiquement
 * - Si non trouvé, PHP génère une erreur de classe non trouvée
 * 
 * Avantages:
 * - Pas besoin de require_once pour chaque classe
 * - Chargement à la demande (lazy loading)
 * - Code plus propre et maintenable
 * - Respect du principe de séparation des responsabilités
 * 
 * Répertoires surveillés:
 * - src/controllers/ : Contrôleurs MVC (AuthController, HomeController, etc.)
 * - src/models/ : Modèles de données (User, Book, Message)
 * - src/services/ : Services utilitaires (Database, etc.)
 * 
 * @package TomTroc\Config
 * @author TomTroc
 * @version 1.0
 */

// Enregistrement de la fonction d'autoloading auprès de PHP
// spl_autoload_register() permet d'empiler plusieurs autoloaders si nécessaire
spl_autoload_register(function ($class) {
    // Liste des répertoires où chercher les fichiers de classes
    // L'ordre est important : les contrôleurs sont recherchés en premier
    $directories = [
        ROOT . DS . 'src' . DS . 'controllers' . DS,  // Contrôleurs (HomeController.php, etc.)
        ROOT . DS . 'src' . DS . 'models' . DS,       // Modèles (User.php, Book.php, etc.)
        ROOT . DS . 'src' . DS . 'services' . DS,     // Services (Database.php, etc.)
    ];

    // Parcourir chaque répertoire pour trouver la classe demandée
    foreach ($directories as $directory) {
        // Construire le chemin complet du fichier
        // Convention: Le nom de la classe doit correspondre au nom du fichier
        // Exemple: class User -> User.php, class HomeController -> HomeController.php
        $file = $directory . $class . '.php';
        
        // Vérifier si le fichier existe dans ce répertoire
        if (file_exists($file)) {
            // Charger le fichier de classe (une seule fois)
            require_once $file;
            // Arrêter la recherche dès que la classe est trouvée
            return;
        }
    }
    
    // Si aucun fichier n'est trouvé dans tous les répertoires,
    // PHP générera automatiquement une erreur "Class not found"
});

// Chargement manuel de la classe Database
// Effectué ici pour garantir sa disponibilité dès le démarrage de l'application,
// car elle est utilisée par la classe Model de base dont héritent tous les modèles
require_once ROOT . DS . 'src' . DS . 'services' . DS . 'Database.php';

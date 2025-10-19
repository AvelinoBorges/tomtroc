<?php
/**
 * Autoloader pour charger automatiquement les classes
 */

spl_autoload_register(function ($class) {
    // Répertoires où chercher les classes
    $directories = [
        ROOT . DS . 'src' . DS . 'controllers' . DS,
        ROOT . DS . 'src' . DS . 'models' . DS,
        ROOT . DS . 'src' . DS . 'services' . DS,
    ];

    // Parcourir chaque répertoire
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Chargement de la classe Database
require_once ROOT . DS . 'src' . DS . 'services' . DS . 'Database.php';

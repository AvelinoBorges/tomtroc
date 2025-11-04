<?php
/**
 * Configuration de la connexion à la base de données
 * 
 * Ce fichier centralise tous les paramètres de connexion MySQL utilisés
 * par PDO pour établir la connexion à la base de données TomTroc.
 * 
 * Sécurité:
 * - En production, ce fichier doit être protégé et exclu du contrôle de version
 * - Les credentials doivent être stockés dans des variables d'environnement
 * - Ne jamais commiter les vrais mots de passe sur Git
 * 
 * Configuration actuelle: Environnement de développement local (WAMP)
 * 
 * Structure retournée:
 * Ce fichier retourne un tableau associatif qui sera utilisé par:
 * - Database::getInstance() dans src/services/Database.php
 * - Model::getPdo() dans src/models/Model.php
 * 
 * @package TomTroc\Config
 * @author TomTroc
 * @version 1.0
 */

return [
    // Hôte MySQL - Serveur de base de données
    // 'localhost' pour développement local, IP ou domaine pour serveur distant
    'host' => 'localhost',
    
    // Nom de la base de données
    // Base de données principale de l'application TomTroc
    'dbname' => 'tomtroc',
    
    // Nom d'utilisateur MySQL
    // 'root' par défaut sur WAMP/XAMPP, utilisateur dédié en production
    'username' => 'root',
    
    // Mot de passe MySQL
    // Vide par défaut sur WAMP/XAMPP, mot de passe fort obligatoire en production
    'password' => '',
    
    // Encodage des caractères
    // utf8mb4 recommandé : Support complet UTF-8 incluant les emojis
    'charset' => 'utf8mb4',
    
    // Options PDO pour configurer le comportement de la connexion
    'options' => [
        // Mode d'erreur : Lance des exceptions en cas d'erreur SQL
        // Permet une meilleure gestion des erreurs avec try/catch
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        
        // Mode de récupération par défaut : Retourne les résultats en tableaux associatifs
        // Permet d'accéder aux colonnes par leur nom : $row['pseudo']
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        
        // Désactive l'émulation des prepared statements
        // Utilise les vrais prepared statements du serveur MySQL pour plus de sécurité
        // Prévient certains types d'injections SQL
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

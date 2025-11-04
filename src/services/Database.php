<?php
/**
 * Classe de gestion de la connexion à la base de données
 * 
 * Cette classe implémente le pattern Singleton pour garantir une seule connexion
 * PDO active dans toute l'application. Elle fournit également des méthodes
 * utilitaires pour tester la connexion et initialiser le schéma de base de données.
 * 
 * Pattern Singleton:
 * - Une seule instance PDO partagée dans toute l'application
 * - Optimisation des ressources (pas de connexions multiples)
 * - Accès global via Database::getInstance()
 * 
 * Fonctionnalités:
 * - Connexion MySQL via PDO avec gestion d'erreurs
 * - Configuration externalisée (config/database.php)
 * - Test de connexion pour vérification de disponibilité
 * - Création automatique du schéma depuis fichier SQL
 * 
 * Sécurité:
 * - Utilisation de PDO pour prévenir les injections SQL
 * - Gestion centralisée des credentials
 * - Mode d'erreur PDO configuré (ERRMODE_EXCEPTION)
 * 
 * @package TomTroc\Services
 * @author TomTroc
 * @version 1.0
 */

class Database
{
    /**
     * Instance unique PDO (Pattern Singleton)
     * 
     * Stocke la connexion PDO partagée dans toute l'application.
     * Initialisée lors du premier appel à getInstance().
     * 
     * @var PDO|null Instance PDO ou null si pas encore connecté
     */
    private static ?PDO $instance = null;
    
    /**
     * Obtient l'instance PDO unique (Pattern Singleton)
     * 
     * Crée la connexion lors du premier appel, puis retourne toujours
     * la même instance pour les appels suivants. La configuration est
     * chargée depuis config/database.php.
     * 
     * Paramètres de connexion:
     * - host: Serveur MySQL (généralement localhost)
     * - dbname: Nom de la base de données (tomtroc)
     * - charset: Encodage des caractères (utf8mb4)
     * - username/password: Credentials MySQL
     * - options: Options PDO (mode d'erreur, fetch mode, etc.)
     * 
     * @return PDO Instance PDO configurée et connectée
     * @throws Exception Si la connexion échoue (credentials invalides, serveur inaccessible)
     */
    public static function getInstance(): PDO
    {
        // Vérification si l'instance existe déjà (lazy loading)
        if (self::$instance === null) {
            // Chargement de la configuration de base de données
            // Le fichier retourne un tableau avec host, dbname, username, password, charset, options
            $config = require ROOT . DS . 'config' . DS . 'database.php';
            
            try {
                // Création de l'instance PDO avec DSN MySQL
                // DSN format: mysql:host=localhost;dbname=tomtroc;charset=utf8mb4
                self::$instance = new PDO(
                    "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // En cas d'échec, lance une exception avec message d'erreur explicite
                // Causes possibles: credentials invalides, serveur MySQL arrêté, base inexistante
                throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }
        
        // Retourne l'instance existante ou nouvellement créée
        return self::$instance;
    }
    
    /**
     * Teste la disponibilité de la connexion à la base de données
     * 
     * Méthode utilitaire pour vérifier que la connexion MySQL fonctionne
     * correctement. Utilisée principalement pour:
     * - Scripts de vérification/monitoring
     * - Tests automatisés
     * - Pages d'administration
     * 
     * Exécute une requête simple (SELECT 1) pour valider la connexion.
     * 
     * @return bool True si la connexion est établie, false en cas d'erreur
     */
    public static function testConnection(): bool
    {
        try {
            // Récupération de l'instance PDO
            $pdo = self::getInstance();
            // Exécution d'une requête simple pour tester la connexion
            // SELECT 1 est une requête légère qui ne fait que vérifier la connectivité
            $pdo->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            // En cas d'erreur (serveur arrêté, timeout, etc.), retourne false
            return false;
        }
    }
    
    /**
     * Crée le schéma de base de données depuis un fichier SQL
     * 
     * Cette méthode lit et exécute le fichier database/schema.sql pour créer:
     * - La base de données tomtroc
     * - Les tables (utilisateurs, livres, messages)
     * - Les clés primaires et étrangères
     * - Les index pour optimisation
     * - Les données de test éventuelles
     * 
     * Utilisée lors de:
     * - L'installation initiale de l'application
     * - La réinitialisation de la base (développement)
     * - Les tests automatisés nécessitant une base propre
     * 
     * Note: Se connecte sans spécifier de base de données pour pouvoir
     * exécuter CREATE DATABASE si nécessaire.
     * 
     * @return bool True si le schéma a été créé avec succès, false en cas d'erreur
     */
    public static function createSchema(): bool
    {
        try {
            // Chargement de la configuration de base de données
            $config = require ROOT . DS . 'config' . DS . 'database.php';
            
            // Connexion MySQL sans spécifier de base de données
            // Nécessaire pour pouvoir exécuter CREATE DATABASE dans le script SQL
            // DSN format: mysql:host=localhost;charset=utf8mb4 (sans dbname)
            $pdo = new PDO(
                "mysql:host={$config['host']};charset={$config['charset']}",
                $config['username'],
                $config['password'],
                $config['options']
            );
            
            // Construction du chemin vers le fichier de schéma SQL
            $sqlFile = ROOT . DS . 'database' . DS . 'schema.sql';
            
            // Vérification de l'existence du fichier
            if (!file_exists($sqlFile)) {
                throw new Exception("Le fichier schema.sql n'existe pas");
            }
            
            // Lecture complète du contenu du fichier SQL
            // Contient les instructions CREATE DATABASE, CREATE TABLE, INSERT, etc.
            $sql = file_get_contents($sqlFile);
            
            // Exécution du script SQL complet
            // PDO::exec() exécute une instruction SQL et retourne le nombre de lignes affectées
            // Adapté pour les DDL (CREATE, DROP, ALTER) et DML sans résultat (INSERT, UPDATE)
            $pdo->exec($sql);
            
            return true;
        } catch (Exception $e) {
            // En cas d'erreur, log dans le fichier d'erreurs PHP
            // Erreurs possibles: permissions insuffisantes, syntaxe SQL invalide, etc.
            error_log("Erreur lors de la création du schéma : " . $e->getMessage());
            return false;
        }
    }
}

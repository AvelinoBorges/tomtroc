<?php
/**
 * Classe Model - Modèle de base abstrait
 * 
 * Cette classe abstraite sert de base pour tous les modèles de l'application.
 * Elle fournit une connexion PDO partagée et des méthodes utilitaires pour
 * interagir avec la base de données.
 * 
 * Pattern Active Record:
 * - Chaque modèle représente une table de la base de données
 * - Les modèles enfants définissent leur table dans $this->table
 * - Méthodes CRUD encapsulées dans chaque modèle
 * 
 * Connexion PDO:
 * - Instance statique partagée entre tous les modèles
 * - Lazy loading: connexion créée au premier appel
 * - Configuration externalisée (config/database.php)
 * 
 * Modèles enfants:
 * - User: Gestion des utilisateurs et authentification
 * - Book: Gestion des livres et recherches
 * - Message: Système de messagerie entre utilisateurs
 * 
 * @package TomTroc\Models
 * @author TomTroc
 * @version 1.0
 */

abstract class Model
{
    /**
     * Instance PDO partagée (Pattern Singleton)
     * 
     * Stocke la connexion à la base de données utilisée par tous les modèles.
     * L'instance est statique pour être partagée entre toutes les classes enfants.
     * 
     * @var PDO|null Instance PDO ou null si pas encore initialisée
     */
    protected static ?PDO $pdo = null;
    
    /**
     * Obtient l'instance PDO pour les requêtes de base de données
     * 
     * Crée la connexion lors du premier appel (lazy loading), puis retourne
     * toujours la même instance. Configuration chargée depuis config/database.php.
     * 
     * Utilisée par tous les modèles enfants pour exécuter leurs requêtes SQL.
     * 
     * @return PDO Instance PDO configurée et connectée
     * @throws Exception Si la connexion échoue
     */
    protected static function getPdo(): PDO
    {
        // Vérification si l'instance existe déjà (lazy loading)
        if (self::$pdo === null) {
            // Chargement de la configuration de base de données
            // Retourne un tableau avec host, dbname, username, password, charset, options
            $config = require_once ROOT . DS . 'config' . DS . 'database.php';
            
            try {
                // Création de la connexion PDO avec DSN MySQL
                self::$pdo = new PDO(
                    "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // En cas d'erreur de connexion, lance une exception
                throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }
        
        // Retourne l'instance PDO
        return self::$pdo;
    }
    
    /**
     * Exécute une requête SQL préparée
     * 
     * Méthode utilitaire pour simplifier l'exécution de requêtes SQL
     * avec des paramètres liés (prepared statements).
     * 
     * Sécurité:
     * - Utilise les prepared statements pour prévenir les injections SQL
     * - Les paramètres sont automatiquement échappés par PDO
     * 
     * Utilisation:
     * $stmt = self::query("SELECT * FROM users WHERE id = ?", [$userId]);
     * $user = $stmt->fetch();
     * 
     * @param string $sql Requête SQL avec placeholders (? ou :nom)
     * @param array $params Paramètres à lier à la requête
     * @return PDOStatement Statement exécuté, prêt pour fetch()
     */
    protected static function query(string $sql, array $params = []): PDOStatement
    {
        // Récupération de l'instance PDO
        $pdo = self::getPdo();
        
        // Préparation de la requête SQL (compilation)
        $stmt = $pdo->prepare($sql);
        
        // Exécution avec les paramètres liés
        $stmt->execute($params);
        
        // Retourne le statement pour récupération des résultats
        return $stmt;
    }
}

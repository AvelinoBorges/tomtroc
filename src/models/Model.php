<?php
/**
 * Modèle de base
 * Tous les modèles héritent de cette classe
 */

abstract class Model
{
    /**
     * Instance PDO pour la connexion à la base de données
     * 
     * @var PDO|null
     */
    protected static ?PDO $pdo = null;
    
    /**
     * Obtient la connexion à la base de données
     * 
     * @return PDO
     */
    protected static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            // Chargement de la configuration
            $config = require_once ROOT . DS . 'config' . DS . 'database.php';
            
            try {
                self::$pdo = new PDO(
                    "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }
        
        return self::$pdo;
    }
    
    /**
     * Exécute une requête SQL
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @return PDOStatement
     */
    protected static function query(string $sql, array $params = []): PDOStatement
    {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt;
    }
}

<?php
/**
 * Classe de gestion de la base de données
 * Fournit des méthodes utilitaires pour la connexion et les opérations de base
 */

class Database
{
    /**
     * Instance PDO
     * 
     * @var PDO|null
     */
    private static ?PDO $instance = null;
    
    /**
     * Obtient l'instance PDO (Singleton)
     * 
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require ROOT . DS . 'config' . DS . 'database.php';
            
            try {
                self::$instance = new PDO(
                    "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Teste la connexion à la base de données
     * 
     * @return bool
     */
    public static function testConnection(): bool
    {
        try {
            $pdo = self::getInstance();
            $pdo->query("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Exécute le script SQL de création de la base de données
     * 
     * @return bool
     */
    public static function createSchema(): bool
    {
        try {
            $config = require ROOT . DS . 'config' . DS . 'database.php';
            
            // Connexion sans spécifier de base de données
            $pdo = new PDO(
                "mysql:host={$config['host']};charset={$config['charset']}",
                $config['username'],
                $config['password'],
                $config['options']
            );
            
            // Lecture du fichier SQL
            $sqlFile = ROOT . DS . 'database' . DS . 'schema.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("Le fichier schema.sql n'existe pas");
            }
            
            $sql = file_get_contents($sqlFile);
            
            // Exécution du script SQL
            $pdo->exec($sql);
            
            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de la création du schéma : " . $e->getMessage());
            return false;
        }
    }
}

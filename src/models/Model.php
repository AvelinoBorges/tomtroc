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
            // Configuration de la base de données (à adapter selon vos besoins)
            $host = 'localhost';
            $dbname = 'tomtroc';
            $username = 'root';
            $password = '';
            
            try {
                self::$pdo = new PDO(
                    "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }
        
        return self::$pdo;
    }
}

<?php

class Book extends Model
{
    protected string $table = 'livres';
    protected PDO $db;

    public function __construct()
    {
        $this->db = self::getPdo();
    }

    /**
     * Recherche tous les livres d'un utilisateur
     * 
     * @param int $userId
     * @return array
     */
    public function findByUserId(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE utilisateur_id = :utilisateur_id ORDER BY date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utilisateur_id' => $userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Recherche un livre par ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $book = $stmt->fetch();
        return $book ?: null;
    }

    /**
     * Recherche un livre par ID avec les informations complètes du propriétaire
     * 
     * @param int $id
     * @return array|null
     */
    public function findByIdWithOwner(int $id): ?array
    {
        $sql = "SELECT l.*, 
                       u.id as owner_id,
                       u.pseudo as owner_username,
                       u.email as owner_email,
                       u.photo as owner_photo,
                       u.date_creation as owner_since
                FROM {$this->table} l 
                INNER JOIN utilisateurs u ON l.utilisateur_id = u.id 
                WHERE l.id = :id 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $book = $stmt->fetch();
        return $book ?: null;
    }

    /**
     * Recherche tous les livres disponibles
     * 
     * @return array
     */
    public function findAllAvailable(): array
    {
        $sql = "SELECT l.*, u.pseudo 
                FROM {$this->table} l 
                INNER JOIN utilisateurs u ON l.utilisateur_id = u.id 
                WHERE l.disponible = 1 
                ORDER BY l.date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Recherche les derniers livres ajoutés
     * 
     * @param int $limit Nombre de livres à retourner
     * @return array
     */
    public function findLatest(int $limit = 4): array
    {
        $sql = "SELECT l.*, u.pseudo 
                FROM {$this->table} l 
                INNER JOIN utilisateurs u ON l.utilisateur_id = u.id 
                WHERE l.disponible = 1 
                ORDER BY l.date_creation DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }

    /**
     * Recherche tous les livres
     * 
     * @return array
     */
    public function findAll(): array
    {
        $sql = "SELECT l.*, u.pseudo 
                FROM {$this->table} l 
                INNER JOIN utilisateurs u ON l.utilisateur_id = u.id 
                ORDER BY l.date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Cria um novo livro
     * 
     * @param array $data
     * @return int|false ID do livro criado ou false em caso de erro
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->table} (utilisateur_id, titre, auteur, description, photo, disponible) 
                VALUES (:utilisateur_id, :titre, :auteur, :description, :photo, :disponible)";
        
        $stmt = $this->db->prepare($sql);
        
        $params = [
            'utilisateur_id' => $data['utilisateur_id'],
            'titre' => $data['titre'],
            'auteur' => $data['auteur'],
            'description' => $data['description'] ?? null,
            'photo' => $data['photo'] ?? null,
            'disponible' => $data['disponible'] ?? 1
        ];
        
        if ($stmt->execute($params)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Met à jour un livre
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];
        
        if (isset($data['titre'])) {
            $fields[] = 'titre = :titre';
            $params['titre'] = $data['titre'];
        }
        
        if (isset($data['auteur'])) {
            $fields[] = 'auteur = :auteur';
            $params['auteur'] = $data['auteur'];
        }
        
        if (isset($data['description'])) {
            $fields[] = 'description = :description';
            $params['description'] = $data['description'];
        }
        
        if (isset($data['photo'])) {
            $fields[] = 'photo = :photo';
            $params['photo'] = $data['photo'];
        }
        
        if (isset($data['disponible'])) {
            $fields[] = 'disponible = :disponible';
            $params['disponible'] = $data['disponible'];
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }

    /**
     * Supprime un livre
     * 
     * @param int $id
     * @param int $userId ID de l'utilisateur (pour vérifier la propriété)
     * @return bool
     */
    public function delete(int $id, int $userId): bool
    {
        // Vérifier si le livre appartient à l'utilisateur
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND utilisateur_id = :utilisateur_id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'utilisateur_id' => $userId
        ]);
    }

    /**
     * Compte combien de livres un utilisateur possède
     * 
     * @param int $userId
     * @return int
     */
    public function countByUserId(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE utilisateur_id = :utilisateur_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utilisateur_id' => $userId]);
        
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Recherche des livres par terme de recherche
     * 
     * @param string $searchTerm
     * @return array
     */
    public function search(string $searchTerm): array
    {
        $sql = "SELECT l.*, u.pseudo 
                FROM {$this->table} l 
                INNER JOIN utilisateurs u ON l.utilisateur_id = u.id 
                WHERE (l.titre LIKE ? OR l.auteur LIKE ? OR l.description LIKE ?) 
                AND l.disponible = 1
                ORDER BY l.date_creation DESC";
        
        $stmt = $this->db->prepare($sql);
        $searchPattern = "%{$searchTerm}%";
        $stmt->execute([$searchPattern, $searchPattern, $searchPattern]);
        
        return $stmt->fetchAll();
    }
}

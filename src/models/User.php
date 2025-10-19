<?php

class User extends Model
{
    protected string $table = 'utilisateurs';
    protected PDO $db;

    public function __construct()
    {
        $this->db = self::getPdo();
    }

    /**
     * Encontra um usuário pelo email
     * 
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND actif = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Encontra um usuário pelo ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND actif = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Cria um novo usuário
     * 
     * @param array $data
     * @return int|false ID do usuário criado ou false em caso de erro
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO {$this->table} (pseudo, email, mot_de_passe, nom, prenom) 
                VALUES (:pseudo, :email, :mot_de_passe, :nom, :prenom)";
        
        $stmt = $this->db->prepare($sql);
        
        $params = [
            'pseudo' => $data['pseudo'],
            'email' => $data['email'],
            'mot_de_passe' => password_hash($data['password'], PASSWORD_DEFAULT),
            'nom' => $data['nom'] ?? null,
            'prenom' => $data['prenom'] ?? null
        ];
        
        if ($stmt->execute($params)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Vérifie si un email existe déjà
     * 
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Vérifie si un pseudo existe déjà
     * 
     * @param string $pseudo
     * @return bool
     */
    public function pseudoExists(string $pseudo): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE pseudo = :pseudo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pseudo' => $pseudo]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Vérifie les identifiants de connexion
     * 
     * @param string $email
     * @param string $password
     * @return array|null Renvoie les données de l'utilisateur si les identifiants sont valides, null sinon
     */
    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Supprimer le mot de passe du tableau avant de retourner
            unset($user['mot_de_passe']);
            return $user;
        }
        
        return null;
    }

    /**
     * Met à jour les informations de l'utilisateur
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = ['id' => $id];
        
        if (isset($data['pseudo'])) {
            $fields[] = 'pseudo = :pseudo';
            $params['pseudo'] = $data['pseudo'];
        }
        
        if (isset($data['email'])) {
            $fields[] = 'email = :email';
            $params['email'] = $data['email'];
        }
        
        if (isset($data['nom'])) {
            $fields[] = 'nom = :nom';
            $params['nom'] = $data['nom'];
        }
        
        if (isset($data['prenom'])) {
            $fields[] = 'prenom = :prenom';
            $params['prenom'] = $data['prenom'];
        }
        
        if (isset($data['photo'])) {
            $fields[] = 'photo = :photo';
            $params['photo'] = $data['photo'];
        }
        
        if (isset($data['password'])) {
            $fields[] = 'mot_de_passe = :mot_de_passe';
            $params['mot_de_passe'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }
}

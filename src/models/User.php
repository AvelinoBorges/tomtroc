<?php

/**
 * Modèle User - Gestion des utilisateurs
 * 
 * Ce modèle gère toutes les opérations relatives aux utilisateurs:
 * - Authentification (login, vérification des credentials)
 * - Création de comptes (inscription avec hachage de mot de passe)
 * - Mise à jour des profils (pseudo, email, photo, informations personnelles)
 * - Recherche d'utilisateurs (par ID, par email)
 * - Validation (unicité de l'email et du pseudo)
 * 
 * Table associée: utilisateurs
 * Champs principaux:
 * - id: Identifiant unique
 * - pseudo: Nom d'utilisateur public
 * - email: Adresse email (unique)
 * - mot_de_passe: Hash bcrypt du mot de passe
 * - nom, prenom: Informations personnelles optionnelles
 * - photo: Chemin vers la photo de profil
 * - actif: Indicateur de compte actif (soft delete)
 * - date_creation: Date d'inscription
 * 
 * Sécurité:
 * - Hachage bcrypt des mots de passe (password_hash avec PASSWORD_DEFAULT)
 * - Vérification sécurisée avec password_verify()
 * - Prepared statements pour toutes les requêtes
 * - Suppression du mot de passe dans les données retournées
 * 
 * @package TomTroc\Models
 * @author TomTroc
 * @version 1.0
 */

class User extends Model
{
    /**
     * Nom de la table en base de données
     * @var string
     */
    protected string $table = 'utilisateurs';
    
    /**
     * Instance PDO pour les requêtes
     * @var PDO
     */
    protected PDO $db;

    /**
     * Constructeur - Initialise la connexion PDO
     */
    public function __construct()
    {
        $this->db = self::getPdo();
    }

    /**
     * Recherche un utilisateur par son adresse email
     * 
     * Utilisée principalement pour:
     * - La connexion (vérification de l'existence du compte)
     * - La validation lors de l'inscription (email unique)
     * - La récupération de mot de passe
     * 
     * Ne retourne que les comptes actifs (actif = 1).
     * 
     * @param string $email Adresse email à rechercher
     * @return array|null Données de l'utilisateur ou null si non trouvé
     */
    public function findByEmail(string $email): ?array
    {
        // Requête avec prepared statement pour sécurité
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND actif = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        // Récupération du résultat
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Recherche un utilisateur par son ID
     * 
     * Utilisée pour:
     * - Affichage des profils publics
     * - Vérification de l'existence d'un utilisateur
     * - Récupération des informations du propriétaire d'un livre
     * - Affichage des participants dans une conversation
     * 
     * Ne retourne que les comptes actifs (actif = 1).
     * 
     * @param int $id Identifiant de l'utilisateur
     * @return array|null Données de l'utilisateur ou null si non trouvé
     */
    public function findById(int $id): ?array
    {
        // Requête avec prepared statement pour sécurité
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND actif = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        // Récupération du résultat
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Crée un nouveau compte utilisateur
     * 
     * Processus d'inscription:
     * 1. Réception des données du formulaire
     * 2. Hachage du mot de passe avec bcrypt
     * 3. Insertion en base de données
     * 4. Retour de l'ID du nouvel utilisateur
     * 
     * Sécurité:
     * - Le mot de passe est haché avec PASSWORD_DEFAULT (bcrypt)
     * - Salage automatique par password_hash()
     * - Coût adaptatif selon la puissance du serveur
     * 
     * Avant d'appeler cette méthode, vérifier:
     * - Unicité de l'email (emailExists)
     * - Unicité du pseudo (pseudoExists)
     * - Longueur minimale du mot de passe (6 caractères)
     * - Correspondance mot de passe / confirmation
     * 
     * @param array $data Données du nouvel utilisateur ['pseudo', 'email', 'password', 'nom', 'prenom']
     * @return int|false ID de l'utilisateur créé ou false en cas d'erreur
     */
    public function create(array $data)
    {
        // Requête d'insertion
        $sql = "INSERT INTO {$this->table} (pseudo, email, mot_de_passe, nom, prenom) 
                VALUES (:pseudo, :email, :mot_de_passe, :nom, :prenom)";
        
        $stmt = $this->db->prepare($sql);
        
        // Préparation des paramètres
        // IMPORTANT: Hachage du mot de passe avec bcrypt
        $params = [
            'pseudo' => $data['pseudo'],
            'email' => $data['email'],
            'mot_de_passe' => password_hash($data['password'], PASSWORD_DEFAULT), // Hachage bcrypt sécurisé
            'nom' => $data['nom'] ?? null,
            'prenom' => $data['prenom'] ?? null
        ];
        
        // Exécution de la requête
        if ($stmt->execute($params)) {
            // Retourne l'ID auto-incrémenté du nouvel utilisateur
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Vérifie si un email existe déjà dans la base de données
     * 
     * Utilisée lors de l'inscription pour garantir l'unicité de l'email.
     * Prévient la création de comptes avec des emails dupliqués.
     * 
     * @param string $email Adresse email à vérifier
     * @return bool True si l'email existe déjà, false sinon
     */
    public function emailExists(string $email): bool
    {
        // Compte le nombre d'utilisateurs avec cet email
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Vérifie si un pseudo existe déjà dans la base de données
     * 
     * Utilisée lors de l'inscription pour garantir l'unicité du pseudo.
     * Le pseudo est le nom d'utilisateur public affiché sur le site.
     * 
     * @param string $pseudo Pseudo à vérifier
     * @return bool True si le pseudo existe déjà, false sinon
     */
    public function pseudoExists(string $pseudo): bool
    {
        // Compte le nombre d'utilisateurs avec ce pseudo
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE pseudo = :pseudo";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['pseudo' => $pseudo]);
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Vérifie les identifiants de connexion (authentification)
     * 
     * Processus de vérification:
     * 1. Recherche de l'utilisateur par email
     * 2. Vérification du mot de passe avec password_verify()
     * 3. Suppression du hash du mot de passe des données retournées
     * 4. Retour des informations de l'utilisateur (sans mot de passe)
     * 
     * Sécurité:
     * - password_verify() compare de manière sécurisée le mot de passe
     * - Protection contre les attaques de timing
     * - Le hash n'est jamais exposé au code appelant
     * 
     * Utilisée dans AuthController::processLogin pour la connexion.
     * 
     * @param string $email Adresse email de connexion
     * @param string $password Mot de passe en clair saisi par l'utilisateur
     * @return array|null Données de l'utilisateur (sans mot de passe) si valide, null sinon
     */
    public function verifyCredentials(string $email, string $password): ?array
    {
        // Recherche de l'utilisateur par email
        $user = $this->findByEmail($email);
        
        // Vérification du mot de passe avec password_verify()
        // Cette fonction compare de manière sécurisée le mot de passe saisi avec le hash stocké
        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // SÉCURITÉ: Suppression du hash du mot de passe avant de retourner les données
            // Le mot de passe ne doit jamais être stocké en session ou exposé
            unset($user['mot_de_passe']);
            return $user;
        }
        
        // Identifiants invalides (email inexistant ou mot de passe incorrect)
        return null;
    }

    /**
     * Met à jour les informations d'un utilisateur
     * 
     * Méthode flexible permettant de mettre à jour un ou plusieurs champs.
     * Seuls les champs présents dans $data sont mis à jour.
     * 
     * Champs modifiables:
     * - pseudo: Nom d'utilisateur
     * - email: Adresse email
     * - nom, prenom: Informations personnelles
     * - photo: Chemin vers la photo de profil
     * - password: Nouveau mot de passe (sera haché automatiquement)
     * 
     * Sécurité:
     * - Si password est fourni, il est automatiquement haché avec bcrypt
     * - Requête dynamique construite uniquement avec les champs fournis
     * - Prepared statements pour prévenir les injections SQL
     * 
     * @param int $id ID de l'utilisateur à mettre à jour
     * @param array $data Tableau associatif des champs à modifier
     * @return bool True si la mise à jour réussit, false sinon
     */
    public function update(int $id, array $data): bool
    {
        // Tableaux pour construire dynamiquement la requête UPDATE
        $fields = [];
        $params = ['id' => $id];
        
        // Ajout conditionnel des champs à mettre à jour
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
        
        // SÉCURITÉ: Si un nouveau mot de passe est fourni, le hacher avec bcrypt
        if (isset($data['password'])) {
            $fields[] = 'mot_de_passe = :mot_de_passe';
            $params['mot_de_passe'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Si aucun champ à mettre à jour, retourner false
        if (empty($fields)) {
            return false;
        }
        
        // Construction et exécution de la requête UPDATE dynamique
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }
}

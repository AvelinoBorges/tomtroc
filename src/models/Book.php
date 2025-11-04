<?php

/**
 * Modèle Book - Gestion des livres
 * 
 * Ce modèle gère toutes les opérations relatives aux livres de la plateforme:
 * - CRUD complet (création, lecture, mise à jour, suppression)
 * - Recherche et filtrage (par utilisateur, par terme, derniers ajouts)
 * - Gestion de la disponibilité
 * - Statistiques (comptage par utilisateur)
 * 
 * Table associée: livres
 * Champs principaux:
 * - id: Identifiant unique du livre
 * - utilisateur_id: Propriétaire du livre (FK vers utilisateurs)
 * - titre: Titre du livre
 * - auteur: Nom de l'auteur
 * - description: Description ou résumé du livre
 * - photo: Chemin vers l'image de couverture
 * - disponible: Statut de disponibilité (1 = disponible, 0 = prêté/indisponible)
 * - date_creation: Date d'ajout du livre
 * 
 * Fonctionnalités:
 * - Bibliothèque personnelle (tous les livres d'un utilisateur)
 * - Catalogue public (livres disponibles à l'échange)
 * - Recherche full-text (titre, auteur, description)
 * - Affichage sur la page d'accueil (derniers livres)
 * - Fiche détaillée avec informations du propriétaire
 * 
 * Sécurité:
 * - Vérification de propriété pour update/delete
 * - Prepared statements pour toutes les requêtes
 * - Validation des permissions côté contrôleur
 * 
 * @package TomTroc\Models
 * @author TomTroc
 * @version 1.0
 */

class Book extends Model
{
    /**
     * Nom de la table en base de données
     * @var string
     */
    protected string $table = 'livres';
    
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
     * Recherche tous les livres d'un utilisateur (bibliothèque personnelle)
     * 
     * Utilisée pour:
     * - Affichage de "Mes livres" dans le compte utilisateur
     * - Page de profil public d'un utilisateur
     * - Statistiques personnelles
     * 
     * Retourne tous les livres (disponibles et non disponibles) triés par
     * date d'ajout décroissante (les plus récents en premier).
     * 
     * @param int $userId ID du propriétaire des livres
     * @return array Tableau de livres (tableau associatif pour chaque livre)
     */
    public function findByUserId(int $userId): array
    {
        // Sélection de tous les livres d'un utilisateur, triés par date décroissante
        $sql = "SELECT * FROM {$this->table} WHERE utilisateur_id = :utilisateur_id ORDER BY date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utilisateur_id' => $userId]);
        
        // Retourne un tableau de tous les livres (vide si aucun)
        return $stmt->fetchAll();
    }

    /**
     * Recherche un livre par son ID
     * 
     * Utilisée pour:
     * - Affichage de la fiche détaillée d'un livre
     * - Vérification d'existence avant modification
     * - Validation de propriété (update/delete)
     * 
     * Retourne uniquement les informations du livre sans jointure.
     * Pour obtenir aussi les infos du propriétaire, utiliser findByIdWithOwner().
     * 
     * @param int $id Identifiant du livre
     * @return array|null Données du livre ou null si non trouvé
     */
    public function findById(int $id): ?array
    {
        // Sélection d'un livre par son ID
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        // Retourne le livre ou null si non trouvé
        $book = $stmt->fetch();
        return $book ?: null;
    }

    /**
     * Recherche un livre avec les informations complètes du propriétaire
     * 
     * Effectue une jointure avec la table utilisateurs pour récupérer
     * toutes les données du propriétaire en une seule requête.
     * 
     * Utilisée pour:
     * - Affichage de la fiche livre avec informations du propriétaire
     * - Page de détail où on affiche le profil du propriétaire
     * - Génération du bouton "Envoyer un message" au propriétaire
     * 
     * Données retournées:
     * - Toutes les informations du livre (l.*)
     * - owner_id, owner_username, owner_email, owner_photo, owner_since
     * 
     * @param int $id Identifiant du livre
     * @return array|null Livre avec données du propriétaire ou null si non trouvé
     */
    public function findByIdWithOwner(int $id): ?array
    {
        // Jointure avec la table utilisateurs pour récupérer les infos du propriétaire
        // Alias: l = livres, u = utilisateurs
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
        
        // Retourne un tableau avec les champs du livre + champs owner_*
        $book = $stmt->fetch();
        return $book ?: null;
    }

    /**
     * Recherche tous les livres disponibles à l'échange
     * 
     * Filtre uniquement les livres avec disponible = 1.
     * Inclut le pseudo du propriétaire via une jointure.
     * 
     * Utilisée pour:
     * - Page "Nos livres à l'échange"
     * - Catalogue public de la plateforme
     * 
     * Tri par date de création décroissante (plus récents en premier).
     * 
     * @return array Tableau de livres disponibles avec pseudo du propriétaire
     */
    public function findAllAvailable(): array
    {
        // Jointure pour inclure le pseudo du propriétaire
        // Filtre: disponible = 1 (livres disponibles à l'échange)
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
     * Recherche les derniers livres ajoutés sur la plateforme
     * 
     * Utilisée pour afficher les livres récents sur la page d'accueil.
     * Par défaut, retourne les 4 derniers livres disponibles.
     * 
     * Fonctionnalité:
     * - Affichage dynamique des nouveautés
     * - Mise en avant des derniers ajouts de la communauté
     * - Incitation à l'exploration du catalogue
     * 
     * @param int $limit Nombre de livres à retourner (par défaut 4)
     * @return array Tableau des derniers livres avec pseudo du propriétaire
     */
    public function findLatest(int $limit = 4): array
    {
        // Jointure avec utilisateurs pour le pseudo
        // Filtre: disponible = 1
        // Tri: par date décroissante
        // Limite: paramétrable (4 par défaut pour la page d'accueil)
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
     * Recherche tous les livres de la plateforme (administration)
     * 
     * Retourne tous les livres, disponibles ou non, avec le pseudo du propriétaire.
     * 
     * Utilisée pour:
     * - Interface d'administration
     * - Statistiques globales
     * - Export de données
     * 
     * @return array Tableau de tous les livres
     */
    public function findAll(): array
    {
        // Jointure pour inclure le pseudo
        // Pas de filtre de disponibilité (tous les livres)
        // Tri par date décroissante
        $sql = "SELECT l.*, u.pseudo 
                FROM {$this->table} l 
                INNER JOIN utilisateurs u ON l.utilisateur_id = u.id 
                ORDER BY l.date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Crée un nouveau livre dans la base de données
     * 
     * Processus d'ajout:
     * 1. Réception des données du formulaire
     * 2. Upload de l'image de couverture (géré par le contrôleur)
     * 3. Insertion en base avec les informations du livre
     * 4. Retour de l'ID du nouveau livre
     * 
     * Champs requis:
     * - utilisateur_id: Propriétaire du livre
     * - titre: Titre du livre
     * - auteur: Nom de l'auteur
     * 
     * Champs optionnels:
     * - description: Résumé ou commentaire
     * - photo: Chemin vers l'image de couverture
     * - disponible: Statut (1 par défaut = disponible)
     * 
     * @param array $data Données du nouveau livre
     * @return int|false ID du livre créé ou false en cas d'erreur
     */
    public function create(array $data)
    {
        // Requête d'insertion
        $sql = "INSERT INTO {$this->table} (utilisateur_id, titre, auteur, description, photo, disponible) 
                VALUES (:utilisateur_id, :titre, :auteur, :description, :photo, :disponible)";
        
        $stmt = $this->db->prepare($sql);
        
        // Préparation des paramètres avec valeurs par défaut
        $params = [
            'utilisateur_id' => $data['utilisateur_id'],
            'titre' => $data['titre'],
            'auteur' => $data['auteur'],
            'description' => $data['description'] ?? null,  // Optionnel
            'photo' => $data['photo'] ?? null,              // Optionnel
            'disponible' => $data['disponible'] ?? 1        // 1 par défaut (disponible)
        ];
        
        // Exécution et retour de l'ID auto-incrémenté
        if ($stmt->execute($params)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Met à jour les informations d'un livre
     * 
     * Méthode flexible permettant de mettre à jour un ou plusieurs champs.
     * Seuls les champs présents dans $data sont mis à jour.
     * 
     * Champs modifiables:
     * - titre: Titre du livre
     * - auteur: Nom de l'auteur
     * - description: Résumé ou commentaire
     * - photo: Nouvelle image de couverture
     * - disponible: Statut de disponibilité (0 ou 1)
     * 
     * Note: La vérification de propriété doit être faite côté contrôleur
     * avant d'appeler cette méthode.
     * 
     * @param int $id ID du livre à mettre à jour
     * @param array $data Tableau associatif des champs à modifier
     * @return bool True si la mise à jour réussit, false sinon
     */
    public function update(int $id, array $data): bool
    {
        // Tableaux pour construire dynamiquement la requête UPDATE
        $fields = [];
        $params = ['id' => $id];
        
        // Ajout conditionnel des champs à mettre à jour
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
        
        // Si aucun champ à mettre à jour, retourner false
        if (empty($fields)) {
            return false;
        }
        
        // Construction et exécution de la requête UPDATE dynamique
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }

    /**
     * Supprime un livre de la base de données
     * 
     * Sécurité: Vérifie que l'utilisateur est bien le propriétaire du livre
     * avant de le supprimer (clause utilisateur_id dans le WHERE).
     * 
     * Si l'utilisateur n'est pas propriétaire, la suppression échoue
     * (aucune ligne affectée, retourne false).
     * 
     * Note: Suppression définitive (DELETE), pas de soft delete.
     * Les messages liés au livre via echange_id ne sont pas supprimés.
     * 
     * @param int $id ID du livre à supprimer
     * @param int $userId ID de l'utilisateur (vérification de propriété)
     * @return bool True si supprimé, false si non propriétaire ou erreur
     */
    public function delete(int $id, int $userId): bool
    {
        // SÉCURITÉ: Vérification de propriété dans la clause WHERE
        // La suppression ne réussit que si utilisateur_id correspond
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND utilisateur_id = :utilisateur_id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'id' => $id,
            'utilisateur_id' => $userId
        ]);
    }

    /**
     * Compte le nombre de livres d'un utilisateur
     * 
     * Utilisée pour:
     * - Affichage du compteur sur le profil ("15 livres")
     * - Statistiques personnelles
     * - Validation (limite de livres par utilisateur)
     * 
     * Compte tous les livres (disponibles et non disponibles).
     * 
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de livres possédés
     */
    public function countByUserId(int $userId): int
    {
        // Compte tous les livres d'un utilisateur
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE utilisateur_id = :utilisateur_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['utilisateur_id' => $userId]);
        
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Recherche des livres par terme de recherche (full-text)
     * 
     * Effectue une recherche dans les champs:
     * - titre: Titre du livre
     * - auteur: Nom de l'auteur
     * - description: Résumé ou commentaire
     * 
     * Utilisée pour:
     * - Barre de recherche sur la page "Nos livres"
     * - Filtrage du catalogue par mots-clés
     * - Recherche d'un livre ou d'un auteur spécifique
     * 
     * Fonctionnement:
     * - Opérateur LIKE avec wildcards (%) pour recherche partielle
     * - Recherche insensible à la casse (comportement MySQL par défaut)
     * - Ne retourne que les livres disponibles (disponible = 1)
     * - Inclut le pseudo du propriétaire via jointure
     * 
     * @param string $searchTerm Terme à rechercher
     * @return array Tableau des livres correspondants
     */
    public function search(string $searchTerm): array
    {
        // Jointure avec utilisateurs pour le pseudo
        // Recherche avec LIKE dans titre, auteur et description
        // Filtre: disponible = 1 (catalogue public)
        $sql = "SELECT l.*, u.pseudo 
                FROM {$this->table} l 
                INNER JOIN utilisateurs u ON l.utilisateur_id = u.id 
                WHERE (l.titre LIKE ? OR l.auteur LIKE ? OR l.description LIKE ?) 
                AND l.disponible = 1
                ORDER BY l.date_creation DESC";
        
        $stmt = $this->db->prepare($sql);
        
        // Ajout des wildcards % pour recherche partielle
        // Ex: "hugo" trouvera "Victor Hugo", "Les Misérables de Hugo", etc.
        $searchPattern = "%{$searchTerm}%";
        
        // Le même pattern est utilisé pour les 3 champs (titre, auteur, description)
        $stmt->execute([$searchPattern, $searchPattern, $searchPattern]);
        
        return $stmt->fetchAll();
    }
}

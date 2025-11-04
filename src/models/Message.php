<?php

/**
 * Modèle Message - Gestion de la messagerie
 * 
 * Ce modèle gère le système de messagerie privée entre utilisateurs:
 * - Envoi et réception de messages
 * - Gestion des conversations (threads entre deux utilisateurs)
 * - Marquer les messages comme lus/non lus
 * - Comptage des messages non lus (badges de notification)
 * - Suppression de messages
 * 
 * Table associée: messages
 * Champs principaux:
 * - id: Identifiant unique du message
 * - expediteur_id: ID de l'utilisateur qui envoie (FK vers utilisateurs)
 * - destinataire_id: ID de l'utilisateur qui reçoit (FK vers utilisateurs)
 * - contenu: Texte du message
 * - lu: Statut de lecture (0 = non lu, 1 = lu)
 * - date_envoi: Horodatage d'envoi (timestamp)
 * - echange_id: ID du livre concerné par l'échange (optionnel, FK vers livres)
 * 
 * Architecture:
 * - Conversations bidirectionnelles (thread entre deux utilisateurs)
 * - Messages organisés chronologiquement
 * - Système de notification (compteur de non-lus)
 * - Contexte d'échange (lié à un livre spécifique)
 * 
 * Fonctionnalités:
 * - Liste des conversations avec dernier message
 * - Affichage d'une conversation complète
 * - Envoi de nouveaux messages
 * - Marquage automatique comme lu lors de la consultation
 * - Badge de notification dans le header
 * 
 * @package TomTroc\Models
 * @author TomTroc
 * @version 1.0
 */

class Message extends Model
{
    /**
     * Nom de la table en base de données
     * @var string
     */
    protected string $table = 'messages';
    
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
     * Récupère toutes les conversations d'un utilisateur
     * 
     * Retourne la liste des conversations avec:
     * - L'autre utilisateur (interlocuteur)
     * - Son pseudo et sa photo de profil
     * - Le dernier message échangé
     * - Le statut de lecture
     * - La date du dernier message
     * 
     * Utilisée pour:
     * - Affichage de la liste des conversations dans la colonne gauche
     * - Interface principale de la messagerie
     * 
     * Note: Cette requête utilise CASE pour déterminer l'interlocuteur
     * (si je suis l'expéditeur, l'autre est le destinataire et vice-versa).
     * 
     * @param int $userId ID de l'utilisateur connecté
     * @return array Tableau des conversations avec infos de l'interlocuteur
     */
    public function getConversations(int $userId): array
    {
        // Requête complexe pour identifier l'interlocuteur de chaque conversation
        // CASE: Détermine qui est l'autre utilisateur (si je suis expéditeur, prend destinataire, sinon expéditeur)
        $sql = "SELECT 
                    CASE 
                        WHEN m.expediteur_id = ? THEN m.destinataire_id
                        ELSE m.expediteur_id
                    END as other_user_id,
                    u.pseudo,
                    u.photo,
                    m.contenu as last_message,
                    m.date_envoi,
                    m.lu,
                    m.expediteur_id
                FROM {$this->table} m
                INNER JOIN utilisateurs u ON (
                    CASE 
                        WHEN m.expediteur_id = ? THEN m.destinataire_id
                        ELSE m.expediteur_id
                    END = u.id
                )
                WHERE m.expediteur_id = ? OR m.destinataire_id = ?
                GROUP BY 
                    CASE 
                        WHEN m.expediteur_id = ? THEN m.destinataire_id
                        ELSE m.expediteur_id
                    END,
                    u.pseudo, 
                    u.photo,
                    m.contenu,
                    m.date_envoi,
                    m.lu,
                    m.expediteur_id
                ORDER BY m.date_envoi DESC";
        
        $stmt = $this->db->prepare($sql);
        // Le même userId est passé 5 fois pour les différentes clauses CASE et WHERE
        $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Récupère l'historique complet d'une conversation entre deux utilisateurs
     * 
     * Retourne tous les messages échangés entre deux utilisateurs, triés
     * chronologiquement (du plus ancien au plus récent).
     * 
     * Inclut pour chaque message:
     * - Toutes les données du message (id, contenu, date_envoi, lu)
     * - Pseudo et photo de l'expéditeur
     * - Pseudo et photo du destinataire
     * 
     * Utilisée pour:
     * - Affichage de la conversation dans la colonne droite
     * - Interface de chat entre deux utilisateurs
     * - Affichage des bulles de message (gauche/droite selon expéditeur)
     * 
     * @param int $userId1 ID du premier utilisateur
     * @param int $userId2 ID du second utilisateur
     * @return array Tableau des messages de la conversation triés chronologiquement
     */
    public function getConversation(int $userId1, int $userId2): array
    {
        // Double jointure pour récupérer les infos de l'expéditeur ET du destinataire
        // u1 = expéditeur, u2 = destinataire
        $sql = "SELECT m.*, 
                       u1.pseudo as expediteur_pseudo,
                       u1.photo as expediteur_photo,
                       u2.pseudo as destinataire_pseudo,
                       u2.photo as destinataire_photo
                FROM {$this->table} m
                INNER JOIN utilisateurs u1 ON m.expediteur_id = u1.id
                INNER JOIN utilisateurs u2 ON m.destinataire_id = u2.id
                WHERE (m.expediteur_id = ? AND m.destinataire_id = ?)
                   OR (m.expediteur_id = ? AND m.destinataire_id = ?)
                ORDER BY m.date_envoi ASC";
        
        $stmt = $this->db->prepare($sql);
        // Vérifie les messages dans les deux sens (A vers B et B vers A)
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
        
        return $stmt->fetchAll();
    }

    /**
     * Envoie un nouveau message
     * 
     * Crée un nouveau message dans la base de données.
     * Le timestamp est automatiquement défini par MySQL (DEFAULT CURRENT_TIMESTAMP).
     * Le statut lu est à 0 par défaut (non lu).
     * 
     * Utilisée pour:
     * - Envoi de message depuis l'interface de chat
     * - Bouton "Envoyer un message" sur une fiche livre
     * - Réponse dans une conversation existante
     * 
     * Paramètres:
     * - senderId: Utilisateur qui envoie
     * - recipientId: Utilisateur qui reçoit
     * - content: Texte du message
     * - exchangeId: (optionnel) ID du livre concerné par l'échange
     * 
     * @param int $senderId ID de l'expéditeur
     * @param int $recipientId ID du destinataire
     * @param string $content Contenu du message
     * @param int|null $exchangeId ID du livre (optionnel, pour contexte d'échange)
     * @return int|false ID du message créé ou false en cas d'erreur
     */
    public function send(int $senderId, int $recipientId, string $content, ?int $exchangeId = null)
    {
        // Insertion d'un nouveau message
        // date_envoi et lu sont gérés automatiquement par MySQL (TIMESTAMP et DEFAULT 0)
        $sql = "INSERT INTO {$this->table} (expediteur_id, destinataire_id, contenu, echange_id) 
                VALUES (:expediteur_id, :destinataire_id, :contenu, :echange_id)";
        
        $stmt = $this->db->prepare($sql);
        
        // Exécution de l'insertion
        $result = $stmt->execute([
            'expediteur_id' => $senderId,
            'destinataire_id' => $recipientId,
            'contenu' => $content,
            'echange_id' => $exchangeId  // null si message non lié à un livre spécifique
        ]);
        
        // Retourne l'ID du nouveau message ou false en cas d'erreur
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Marque les messages comme lus
     * 
     * Met à jour le statut lu (0 → 1) de tous les messages non lus
     * envoyés par un utilisateur spécifique à l'utilisateur connecté.
     * 
     * Utilisée quand:
     * - L'utilisateur ouvre une conversation
     * - Affichage des messages d'un expéditeur
     * - Réinitialisation du badge de notification pour cette conversation
     * 
     * Permet de:
     * - Indiquer que les messages ont été consultés
     * - Mettre à jour les compteurs de non-lus
     * - Retirer le badge de notification
     * 
     * @param int $userId ID de l'utilisateur qui lit (destinataire)
     * @param int $otherUserId ID de l'utilisateur dont on lit les messages (expéditeur)
     * @return bool True si succès, false sinon
     */
    public function markAsRead(int $userId, int $otherUserId): bool
    {
        // Mise à jour de lu = 1 pour tous les messages non lus
        // Cible: messages reçus par userId et envoyés par otherUserId
        $sql = "UPDATE {$this->table} 
                SET lu = 1 
                WHERE destinataire_id = :user_id 
                AND expediteur_id = :other_user_id 
                AND lu = 0";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'other_user_id' => $otherUserId
        ]);
    }

    /**
     * Compte le nombre total de messages non lus d'un utilisateur
     * 
     * Compte tous les messages reçus par l'utilisateur avec lu = 0.
     * 
     * Utilisée pour:
     * - Badge de notification dans le header (ex: "Messagerie (5)")
     * - Indication visuelle de nouveaux messages
     * - Compteur global de messages non lus
     * 
     * Affichage:
     * - Si > 0: Badge rouge avec le nombre
     * - Si = 0: Pas de badge ou badge masqué
     * 
     * @param int $userId ID de l'utilisateur
     * @return int Nombre de messages non lus
     */
    public function countUnread(int $userId): int
    {
        // Compte tous les messages non lus reçus par l'utilisateur
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE destinataire_id = :user_id AND lu = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Compte les messages non lus d'une conversation spécifique
     * 
     * Compte uniquement les messages non lus provenant d'un expéditeur précis.
     * 
     * Utilisée pour:
     * - Badge sur chaque conversation dans la liste
     * - Indication du nombre de nouveaux messages par conversation
     * - Tri des conversations (mettre en avant celles avec non-lus)
     * 
     * Différence avec countUnread():
     * - countUnread(): Total tous expéditeurs confondus (badge header)
     * - countUnreadFrom(): Uniquement d'un expéditeur (badge conversation)
     * 
     * @param int $userId ID du destinataire (utilisateur qui reçoit)
     * @param int $otherUserId ID de l'expéditeur (utilisateur qui envoie)
     * @return int Nombre de messages non lus de cet expéditeur
     */
    public function countUnreadFrom(int $userId, int $otherUserId): int
    {
        // Compte les messages non lus d'un expéditeur spécifique
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE destinataire_id = :user_id 
                AND expediteur_id = :other_user_id 
                AND lu = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'other_user_id' => $otherUserId
        ]);
        
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Supprime un message
     * 
     * Sécurité: Vérifie que l'utilisateur est soit l'expéditeur soit le
     * destinataire du message avant de le supprimer.
     * 
     * Permissions:
     * - L'expéditeur peut supprimer ses messages envoyés
     * - Le destinataire peut supprimer les messages reçus
     * - Les autres utilisateurs ne peuvent pas supprimer
     * 
     * Note: Suppression définitive (DELETE), pas de soft delete.
     * Une amélioration pourrait être d'implémenter une suppression
     * par utilisateur (marqué comme supprimé pour l'un mais visible pour l'autre).
     * 
     * @param int $messageId ID du message à supprimer
     * @param int $userId ID de l'utilisateur (vérification de permission)
     * @return bool True si supprimé, false si non autorisé ou erreur
     */
    public function delete(int $messageId, int $userId): bool
    {
        // SÉCURITÉ: Vérifie que l'utilisateur est expéditeur OU destinataire
        // La suppression ne réussit que si userId est impliqué dans le message
        $sql = "DELETE FROM {$this->table} 
                WHERE id = ? 
                AND (expediteur_id = ? OR destinataire_id = ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$messageId, $userId, $userId]);
    }

    /**
     * Vérifie si une conversation existe entre deux utilisateurs
     * 
     * Retourne true s'il existe au moins un message échangé entre les deux
     * utilisateurs (dans n'importe quel sens).
     * 
     * Utilisée pour:
     * - Vérifier si une conversation a déjà commencé
     * - Décider d'afficher ou non la conversation dans la liste
     * - Validation avant d'envoyer un message
     * - Redirection vers la conversation existante
     * 
     * @param int $userId ID du premier utilisateur
     * @param int $otherUserId ID du second utilisateur
     * @return bool True si conversation existe, false sinon
     */
    public function hasConversation(int $userId, int $otherUserId): bool
    {
        // Compte les messages échangés dans les deux sens
        // (A vers B) OU (B vers A)
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE (expediteur_id = ? AND destinataire_id = ?)
                   OR (expediteur_id = ? AND destinataire_id = ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $otherUserId, $otherUserId, $userId]);
        
        $result = $stmt->fetch();
        // Retourne true si au moins un message existe
        return (int) $result['count'] > 0;
    }
}

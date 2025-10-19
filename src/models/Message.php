<?php

class Message extends Model
{
    protected string $table = 'messages';
    protected PDO $db;

    public function __construct()
    {
        $this->db = self::getPdo();
    }

    /**
     * Recherche toutes les conversations d'un utilisateur
     * Retourne le dernier message de chaque conversation
     * 
     * @param int $userId
     * @return array
     */
    public function getConversations(int $userId): array
    {
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
        $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Recherche les messages entre deux utilisateurs
     * 
     * @param int $userId1
     * @param int $userId2
     * @return array
     */
    public function getConversation(int $userId1, int $userId2): array
    {
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
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
        
        return $stmt->fetchAll();
    }

    /**
     * Envia uma mensagem
     * 
     * @param int $senderId
     * @param int $recipientId
     * @param string $content
     * @param int|null $exchangeId
     * @return int|false ID da mensagem ou false
     */
    public function send(int $senderId, int $recipientId, string $content, ?int $exchangeId = null)
    {
        $sql = "INSERT INTO {$this->table} (expediteur_id, destinataire_id, contenu, echange_id) 
                VALUES (:expediteur_id, :destinataire_id, :contenu, :echange_id)";
        
        $stmt = $this->db->prepare($sql);
        
        $result = $stmt->execute([
            'expediteur_id' => $senderId,
            'destinataire_id' => $recipientId,
            'contenu' => $content,
            'echange_id' => $exchangeId
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Marque les messages comme lus
     * 
     * @param int $userId Utilisateur qui lit
     * @param int $otherUserId Utilisateur dont on lit les messages
     * @return bool
     */
    public function markAsRead(int $userId, int $otherUserId): bool
    {
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
     * Compte les messages non lus d'un utilisateur
     * 
     * @param int $userId
     * @return int
     */
    public function countUnread(int $userId): int
    {
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
     * @param int $userId
     * @param int $otherUserId
     * @return int
     */
    public function countUnreadFrom(int $userId, int $otherUserId): int
    {
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
     * @param int $messageId
     * @param int $userId ID de l'utilisateur (pour vérifier la permission)
     * @return bool
     */
    public function delete(int $messageId, int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE id = ? 
                AND (expediteur_id = ? OR destinataire_id = ?)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$messageId, $userId, $userId]);
    }

    /**
     * Vérifie si un utilisateur participe à une conversation
     * 
     * @param int $userId
     * @param int $otherUserId
     * @return bool
     */
    public function hasConversation(int $userId, int $otherUserId): bool
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE (expediteur_id = ? AND destinataire_id = ?)
                   OR (expediteur_id = ? AND destinataire_id = ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $otherUserId, $otherUserId, $userId]);
        
        $result = $stmt->fetch();
        return (int) $result['count'] > 0;
    }
}

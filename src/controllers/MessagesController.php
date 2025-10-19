<?php

class MessagesController extends Controller
{
    private $messageModel;
    private $userModel;

    public function __construct()
    {
        $this->messageModel = new Message();
        $this->userModel = new User();
    }

    public function index()
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder aux messages.";
            header('Location: /tomtroc/auth/login');
            exit;
        }

        $currentUserId = $_SESSION['user']['id'];
        $pageTitle = 'Messagerie - TomTroc';

        // Rechercher les conversations de l'utilisateur
        $conversationsData = $this->messageModel->getConversations($currentUserId);

        // Formater les conversations pour la vue
        $conversations = [];
        foreach ($conversationsData as $conv) {
            $conversations[] = [
                'id' => $conv['other_user_id'],
                'username' => $conv['pseudo'],
                'avatar' => $this->formatAvatarPath($conv['photo']),
                'lastMessage' => $this->truncateText($conv['last_message'], 60),
                'time' => $this->formatTime($conv['date_envoi']),
                'unread' => !$conv['lu'] && $conv['expediteur_id'] != $currentUserId
            ];
        }

        // Déterminer la conversation active
        $activeUserId = null;
        $messages = [];
        $activeUser = null;
        $activeAvatar = null;

        // S'il y a un paramètre 'to' dans l'URL, démarrer la conversation avec cet utilisateur
        if (isset($_GET['to'])) {
            $activeUserId = (int)$_GET['to'];
        } 
        // Sinon, utiliser la première conversation de la liste
        elseif (!empty($conversations)) {
            $activeUserId = $conversations[0]['id'];
        }

        // Rechercher les messages de la conversation active
        if ($activeUserId) {
            $activeUserData = $this->userModel->findById($activeUserId);
            
            if ($activeUserData) {
                $activeUser = $activeUserData['pseudo'];
                $activeAvatar = $this->formatAvatarPath($activeUserData['photo']);

                // Rechercher les messages
                $messagesData = $this->messageModel->getConversation($currentUserId, $activeUserId);

                // Formater les messages pour la vue
                foreach ($messagesData as $msg) {
                    $messages[] = [
                        'id' => $msg['id'],
                        'sender' => $msg['expediteur_id'] == $currentUserId ? 'me' : 'other',
                        'text' => $msg['contenu'],
                        'time' => $this->formatTime($msg['date_envoi']),
                        'date' => $this->formatDate($msg['date_envoi'])
                    ];
                }

                // Marquer les messages comme lus
                $this->messageModel->markAsRead($currentUserId, $activeUserId);
            }
        }

        require_once ROOT . DS . 'src' . DS . 'views' . DS . 'messages' . DS . 'index.php';
    }

    public function send()
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tomtroc/messages');
            exit;
        }

        $currentUserId = $_SESSION['user']['id'];
        $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : null;
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';

        // Validation
        if (!$recipientId || empty($message)) {
            $_SESSION['error'] = "Destinataire ou message invalide.";
            header('Location: /tomtroc/messages');
            exit;
        }

        // Vérifier qu'on n'envoie pas à soi-même
        if ($recipientId == $currentUserId) {
            $_SESSION['error'] = "Vous ne pouvez pas vous envoyer un message à vous-même.";
            header('Location: /tomtroc/messages');
            exit;
        }

        // Vérifier si le destinataire existe
        $recipient = $this->userModel->findById($recipientId);
        if (!$recipient) {
            $_SESSION['error'] = "Destinataire non trouvé.";
            header('Location: /tomtroc/messages');
            exit;
        }

        // Envoyer le message
        $messageId = $this->messageModel->send($currentUserId, $recipientId, $message);

        if ($messageId) {
            $_SESSION['success'] = "Message envoyé avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de l'envoi du message.";
        }

        // Rediriger vers la conversation
        header('Location: /tomtroc/messages?to=' . $recipientId);
        exit;
    }

    /**
     * Formate le chemin de l'avatar
     */
    private function formatAvatarPath(?string $photo): string
    {
        if (empty($photo)) {
            return '/tomtroc/public/images/default-avatar.svg';
        }

        if (strpos($photo, 'profiles/') === 0) {
            return '/tomtroc/public/images/' . $photo;
        }

        return '/tomtroc/public/images/profiles/' . $photo;
    }

    /**
     * Tronque le texte pour l'aperçu
     */
    private function truncateText(string $text, int $length): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }

    /**
     * Formate l'heure
     */
    private function formatTime(string $datetime): string
    {
        $date = new DateTime($datetime);
        return $date->format('H:i');
    }

    /**
     * Formate la date
     */
    private function formatDate(string $datetime): string
    {
        $date = new DateTime($datetime);
        return $date->format('d.m');
    }
}


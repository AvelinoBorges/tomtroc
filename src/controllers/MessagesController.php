<?php
/**
 * Contrôleur de gestion complète de la messagerie
 * 
 * Ce contrôleur gère toutes les fonctionnalités de la messagerie de TomTroc :
 * - Affichage de la liste des conversations
 * - Affichage des messages d'une conversation
 * - Envoi de nouveaux messages
 * - Gestion du statut de lecture des messages
 * 
 * Contrairement à MessageController, ce contrôleur utilise la base de données
 * pour récupérer et stocker les messages réels.
 */
class MessagesController extends Controller
{
    /**
     * @var Message Instance du modèle Message pour gérer les messages
     */
    private $messageModel;
    
    /**
     * @var User Instance du modèle User pour gérer les utilisateurs
     */
    private $userModel;

    /**
     * Constructeur du contrôleur
     * 
     * Initialise les modèles nécessaires pour la gestion de la messagerie
     */
    public function __construct()
    {
        $this->messageModel = new Message();
        $this->userModel = new User();
    }

    /**
     * Affiche la page de messagerie avec les conversations et messages
     * 
     * Cette méthode récupère toutes les conversations de l'utilisateur connecté
     * et affiche les messages de la conversation active. Elle gère également
     * le marquage des messages comme lus et permet de démarrer une nouvelle
     * conversation via le paramètre 'to' dans l'URL.
     * 
     * @return void
     */
    public function index()
    {
        // Vérifier si l'utilisateur est authentifié
        // Sans connexion, accès refusé avec redirection vers la page de login
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder aux messages.";
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Récupérer l'identifiant de l'utilisateur actuellement connecté
        $currentUserId = $_SESSION['user']['id'];
        $pageTitle = 'Messagerie - TomTroc';

        // Récupérer toutes les conversations de l'utilisateur depuis la base de données
        // Chaque conversation contient les informations du dernier message échangé
        $conversationsData = $this->messageModel->getConversations($currentUserId);

        // Préparer un tableau formaté des conversations pour l'affichage dans la vue
        $conversations = [];
        foreach ($conversationsData as $conv) {
            $conversations[] = [
                'id' => $conv['other_user_id'],                                      // ID de l'autre utilisateur dans la conversation
                'username' => $conv['pseudo'],                                       // Pseudo de l'autre utilisateur
                'avatar' => $this->formatAvatarPath($conv['photo']),                // Chemin complet vers l'avatar
                'lastMessage' => $this->truncateText($conv['last_message'], 60),   // Aperçu du dernier message (60 caractères max)
                'time' => $this->formatTime($conv['date_envoi']),                   // Heure du dernier message (format HH:MM)
                'unread' => !$conv['lu'] && $conv['expediteur_id'] != $currentUserId  // Indicateur de message non lu
            ];
        }

        // Initialiser les variables pour la conversation active
        $activeUserId = null;      // ID de l'utilisateur avec qui on discute
        $messages = [];            // Tableau des messages de la conversation
        $activeUser = null;        // Pseudo de l'utilisateur actif
        $activeAvatar = null;      // Avatar de l'utilisateur actif

        // Déterminer quelle conversation afficher
        // Priorité 1: Paramètre 'to' dans l'URL (pour démarrer une nouvelle conversation)
        if (isset($_GET['to'])) {
            $activeUserId = (int)$_GET['to'];
        } 
        // Priorité 2: Première conversation de la liste (conversation la plus récente)
        elseif (!empty($conversations)) {
            $activeUserId = $conversations[0]['id'];
        }

        // Charger et afficher les messages de la conversation active
        if ($activeUserId) {
            // Récupérer les informations complètes de l'utilisateur avec qui on discute
            $activeUserData = $this->userModel->findById($activeUserId);
            
            if ($activeUserData) {
                // Stocker les informations de l'utilisateur actif pour l'en-tête de conversation
                $activeUser = $activeUserData['pseudo'];
                $activeAvatar = $this->formatAvatarPath($activeUserData['photo']);

                // Récupérer tous les messages échangés entre les deux utilisateurs
                // Les messages sont ordonnés chronologiquement
                $messagesData = $this->messageModel->getConversation($currentUserId, $activeUserId);

                // Formater chaque message pour l'affichage dans la vue
                foreach ($messagesData as $msg) {
                    $messages[] = [
                        'id' => $msg['id'],                                                          // Identifiant unique du message
                        'sender' => $msg['expediteur_id'] == $currentUserId ? 'me' : 'other',      // Détermine si c'est un message envoyé ou reçu
                        'text' => $msg['contenu'],                                                  // Contenu textuel du message
                        'time' => $this->formatTime($msg['date_envoi']),                           // Heure d'envoi (HH:MM)
                        'date' => $this->formatDate($msg['date_envoi'])                            // Date d'envoi (JJ.MM)
                    ];
                }

                // Marquer tous les messages reçus dans cette conversation comme lus
                // Cela met à jour le statut dans la base de données
                $this->messageModel->markAsRead($currentUserId, $activeUserId);
            }
        }

        // Charger la vue de la messagerie avec toutes les données préparées
        require_once ROOT . DS . 'src' . DS . 'views' . DS . 'messages' . DS . 'index.php';
    }

    /**
     * Traite l'envoi d'un nouveau message
     * 
     * Cette méthode gère la soumission du formulaire d'envoi de message.
     * Elle effectue plusieurs validations de sécurité :
     * - Vérification de l'authentification
     * - Validation de la méthode HTTP (POST uniquement)
     * - Validation du destinataire et du contenu
     * - Vérification de l'existence du destinataire
     * 
     * Après envoi réussi, redirige vers la conversation avec le destinataire.
     * 
     * @return void
     */
    public function send()
    {
        // Vérifier que l'utilisateur est authentifié
        // Un utilisateur non connecté ne peut pas envoyer de messages
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // S'assurer que la requête est de type POST
        // Empêche l'envoi de messages via des liens GET malveillants
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tomtroc/messages');
            exit;
        }

        // Récupérer les données du formulaire
        $currentUserId = $_SESSION['user']['id'];                                      // ID de l'expéditeur (utilisateur connecté)
        $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : null;  // ID du destinataire
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';           // Contenu du message (nettoyé des espaces)

        // Validation des données : destinataire et message obligatoires
        if (!$recipientId || empty($message)) {
            $_SESSION['error'] = "Destinataire ou message invalide.";
            header('Location: /tomtroc/messages');
            exit;
        }

        // Empêcher l'auto-messagerie (envoyer un message à soi-même)
        // Cette vérification évite une situation illogique
        if ($recipientId == $currentUserId) {
            $_SESSION['error'] = "Vous ne pouvez pas vous envoyer un message à vous-même.";
            header('Location: /tomtroc/messages');
            exit;
        }

        // Vérifier que le destinataire existe dans la base de données
        // Protège contre l'envoi à des utilisateurs inexistants ou supprimés
        $recipient = $this->userModel->findById($recipientId);
        if (!$recipient) {
            $_SESSION['error'] = "Destinataire non trouvé.";
            header('Location: /tomtroc/messages');
            exit;
        }

        // Enregistrer le message dans la base de données
        // La méthode retourne l'ID du nouveau message en cas de succès
        $messageId = $this->messageModel->send($currentUserId, $recipientId, $message);

        // Définir le message de feedback pour l'utilisateur
        if ($messageId) {
            $_SESSION['success'] = "Message envoyé avec succès!";
        } else {
            $_SESSION['error'] = "Erreur lors de l'envoi du message.";
        }

        // Rediriger vers la conversation avec le destinataire
        // Le paramètre 'to' permet d'afficher directement cette conversation
        header('Location: /tomtroc/messages?to=' . $recipientId);
        exit;
    }

    /**
     * Formate le chemin complet de l'avatar d'un utilisateur
     * 
     * Transforme le nom de fichier stocké en base de données en chemin
     * d'accès complet pour l'affichage. Retourne un avatar par défaut
     * si aucune photo n'est définie.
     * 
     * @param string|null $photo Le nom du fichier photo (peut être null)
     * @return string Le chemin complet de l'image avatar
     */
    private function formatAvatarPath(?string $photo): string
    {
        // Si aucune photo n'est définie, retourner l'avatar par défaut
        if (empty($photo)) {
            return '/tomtroc/public/images/default-avatar.svg';
        }

        // Si le chemin contient déjà 'profiles/', ajouter seulement le préfixe de base
        if (strpos($photo, 'profiles/') === 0) {
            return '/tomtroc/public/images/' . $photo;
        }

        // Sinon, construire le chemin complet avec le dossier 'profiles/'
        return '/tomtroc/public/images/profiles/' . $photo;
    }

    /**
     * Tronque un texte à une longueur maximale pour l'aperçu
     * 
     * Utilisé pour afficher un extrait du dernier message dans la liste
     * des conversations. Ajoute des points de suspension si le texte
     * est tronqué.
     * 
     * @param string $text Le texte à tronquer
     * @param int $length La longueur maximale désirée
     * @return string Le texte tronqué avec '...' si nécessaire
     */
    private function truncateText(string $text, int $length): string
    {
        // Si le texte est déjà assez court, le retourner tel quel
        if (strlen($text) <= $length) {
            return $text;
        }
        
        // Couper le texte à la longueur spécifiée et ajouter des points de suspension
        return substr($text, 0, $length) . '...';
    }

    /**
     * Formate une date/heure en heure uniquement (HH:MM)
     * 
     * Extrait et formate l'heure depuis une chaîne datetime MySQL
     * pour l'affichage dans l'interface de messagerie.
     * 
     * @param string $datetime La date/heure au format MySQL (YYYY-MM-DD HH:MM:SS)
     * @return string L'heure formatée (HH:MM)
     */
    private function formatTime(string $datetime): string
    {
        $date = new DateTime($datetime);
        return $date->format('H:i');
    }

    /**
     * Formate une date/heure en date courte (JJ.MM)
     * 
     * Extrait et formate la date depuis une chaîne datetime MySQL
     * pour un affichage compact dans l'interface.
     * 
     * @param string $datetime La date/heure au format MySQL (YYYY-MM-DD HH:MM:SS)
     * @return string La date formatée (JJ.MM)
     */
    private function formatDate(string $datetime): string
    {
        $date = new DateTime($datetime);
        return $date->format('d.m');
    }
}


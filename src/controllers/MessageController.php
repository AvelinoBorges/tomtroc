<?php
/**
 * Contrôleur de gestion de la messagerie
 * 
 * Ce contrôleur gère l'affichage de la messagerie et l'envoi de messages
 * entre les utilisateurs de la plateforme TomTroc.
 * Utilise le modèle Message pour interagir avec la base de données.
 * 
 * Fonctionnalités principales :
 * - Affichage de la liste des conversations
 * - Affichage des messages d'une conversation spécifique
 * - Envoi de nouveaux messages
 * - Marquage automatique des messages comme lus
 * - Gestion de la sécurité et des validations
 * 
 * @package TomTroc\Controllers
 * @author TomTroc
 * @version 1.0
 */

class MessageController extends Controller
{
    /**
     * Instance du modèle Message pour gérer les messages
     * 
     * Permet d'accéder aux méthodes de récupération, envoi et gestion
     * des messages stockés dans la base de données.
     * 
     * @var Message
     */
    private $messageModel;
    
    /**
     * Instance du modèle User pour gérer les utilisateurs
     * 
     * Permet de récupérer les informations des utilisateurs
     * (pseudo, photo de profil, etc.) pour l'affichage.
     * 
     * @var User
     */
    private $userModel;

    /**
     * Constructeur du contrôleur
     * 
     * Initialise les modèles nécessaires pour la gestion de la messagerie.
     * Le constructeur est appelé automatiquement lors de l'instanciation
     * du contrôleur par le système de routage.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->messageModel = new Message();
        $this->userModel = new User();
    }

    /**
     * Affiche la page de messagerie avec les conversations et messages
     * 
     * Cette méthode constitue le point d'entrée principal de la messagerie.
     * Elle récupère toutes les conversations de l'utilisateur connecté depuis
     * la base de données et affiche les messages de la conversation active.
     * 
     * Workflow :
     * 1. Vérification de l'authentification de l'utilisateur
     * 2. Récupération de toutes les conversations depuis la base de données
     * 3. Formatage des données pour l'affichage
     * 4. Détermination de la conversation active (paramètre 'to' ou première conversation)
     * 5. Chargement des messages de la conversation active
     * 6. Marquage des messages reçus comme lus
     * 7. Affichage de la vue avec toutes les données
     * 
     * Paramètres URL acceptés :
     * - ?to={user_id} : Affiche la conversation avec l'utilisateur spécifié
     * 
     * @return void Affiche la vue messages/index.php
     */
    public function index()
    {
        // Vérifier si l'utilisateur est authentifié
        // Sans authentification, redirection vers la page de connexion avec message d'erreur
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = "Vous devez être connecté pour accéder aux messages.";
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Récupérer l'identifiant de l'utilisateur actuellement connecté
        $currentUserId = $_SESSION['user']['id'];
        $pageTitle = 'Messagerie - TomTroc';

        // Récupérer toutes les conversations de l'utilisateur depuis la base de données
        // La méthode getConversations retourne un tableau avec les informations
        // de chaque conversation (autre utilisateur, dernier message, statut de lecture)
        $conversationsData = $this->messageModel->getConversations($currentUserId);

        // Formater les conversations pour un affichage optimal dans la vue
        // Transformation des données brutes en format attendu par le template
        $conversations = [];
        foreach ($conversationsData as $conv) {
            $conversations[] = [
                'id' => $conv['other_user_id'],                                      // ID de l'autre utilisateur dans la conversation
                'username' => $conv['pseudo'],                                       // Pseudo de l'interlocuteur
                'avatar' => $this->formatAvatarPath($conv['photo']),                // Chemin complet vers la photo de profil
                'lastMessage' => $this->truncateText($conv['last_message'], 60),   // Aperçu du dernier message (60 caractères max)
                'time' => $this->formatTime($conv['date_envoi']),                   // Heure du dernier message au format HH:MM
                'unread' => !$conv['lu'] && $conv['expediteur_id'] != $currentUserId  // Badge de message non lu
            ];
        }

        // Initialiser les variables pour la conversation active
        // Ces variables seront remplies si une conversation est sélectionnée
        $activeUserId = null;      // ID de l'utilisateur avec qui on discute actuellement
        $messages = [];            // Tableau des messages de la conversation active
        $activeUser = null;        // Pseudo de l'utilisateur actif (affiché en en-tête)
        $activeAvatar = null;      // Avatar de l'utilisateur actif (affiché en en-tête)

        // Déterminer quelle conversation afficher
        // Priorité 1 : Paramètre GET 'to' (pour ouvrir une conversation spécifique)
        // Priorité 2 : Première conversation de la liste (la plus récente)
        if (isset($_GET['to'])) {
            $activeUserId = (int)$_GET['to'];
        } elseif (!empty($conversations)) {
            $activeUserId = $conversations[0]['id'];
        }

        // Charger et afficher les messages de la conversation active
        if ($activeUserId) {
            // Récupérer les informations complètes de l'utilisateur actif
            $activeUserData = $this->userModel->findById($activeUserId);
            
            if ($activeUserData) {
                // Stocker les informations de l'utilisateur pour l'en-tête de la conversation
                $activeUser = $activeUserData['pseudo'];
                $activeAvatar = $this->formatAvatarPath($activeUserData['photo']);

                // Récupérer tous les messages échangés entre les deux utilisateurs
                // Les messages sont ordonnés chronologiquement (du plus ancien au plus récent)
                $messagesData = $this->messageModel->getConversation($currentUserId, $activeUserId);

                // Formater chaque message pour l'affichage dans la vue
                foreach ($messagesData as $msg) {
                    $messages[] = [
                        'id' => $msg['id'],                                                          // Identifiant unique du message
                        'sender' => $msg['expediteur_id'] == $currentUserId ? 'me' : 'other',      // Distinguer message envoyé/reçu
                        'text' => $msg['contenu'],                                                  // Contenu textuel du message
                        'time' => $this->formatTime($msg['date_envoi']),                           // Heure d'envoi (HH:MM)
                        'date' => $this->formatDate($msg['date_envoi'])                            // Date d'envoi (JJ.MM)
                    ];
                }

                // Marquer tous les messages reçus dans cette conversation comme lus
                // Cela met à jour le statut 'lu' dans la base de données
                // et retire le badge de notification pour cette conversation
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
     * Elle effectue plusieurs validations de sécurité avant d'enregistrer
     * le message dans la base de données.
     * 
     * Validations effectuées :
     * - Vérification de l'authentification de l'utilisateur
     * - Validation de la méthode HTTP (POST uniquement)
     * - Validation de la présence du destinataire et du contenu
     * - Vérification que le destinataire existe dans la base de données
     * - Empêcher l'auto-messagerie (s'envoyer un message à soi-même)
     * 
     * En cas de succès :
     * - Le message est enregistré dans la table 'messages'
     * - L'utilisateur est redirigé vers la conversation avec le destinataire
     * - Un message de confirmation est affiché
     * 
     * En cas d'erreur :
     * - L'utilisateur est redirigé vers la messagerie
     * - Un message d'erreur explicite est affiché
     * 
     * @return void Redirige vers /tomtroc/messages?to={recipient_id}
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
        // Empêche l'envoi de messages via des liens GET malveillants (protection CSRF basique)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tomtroc/messages');
            exit;
        }

        // Récupérer les données du formulaire
        $currentUserId = $_SESSION['user']['id'];                                      // ID de l'expéditeur (utilisateur connecté)
        $recipientId = isset($_POST['recipient_id']) ? (int)$_POST['recipient_id'] : null;  // ID du destinataire depuis le formulaire
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';           // Contenu du message (nettoyé des espaces)

        // Validation des données obligatoires
        // Le destinataire et le message doivent être présents et valides
        if (!$recipientId || empty($message)) {
            $_SESSION['error'] = "Destinataire ou message invalide.";
            header('Location: /tomtroc/messages');
            exit;
        }

        // Empêcher l'auto-messagerie (envoyer un message à soi-même)
        // Cette vérification évite une situation illogique dans l'interface
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
        // La méthode send() retourne l'ID du nouveau message en cas de succès, false sinon
        $messageId = $this->messageModel->send($currentUserId, $recipientId, $message);

        // Définir le message de feedback approprié pour l'utilisateur
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
     * d'accès complet pour l'affichage dans les vues. Gère plusieurs cas :
     * - Photo null ou vide : retourne l'avatar par défaut
     * - Photo avec préfixe 'profiles/' : ajoute seulement le préfixe de base
     * - Photo simple : construit le chemin complet
     * 
     * Utilisée pour :
     * - Affichage des avatars dans la liste des conversations
     * - Affichage de l'avatar en en-tête de conversation
     * - Garantir un affichage cohérent des images de profil
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
     * Utilisée pour afficher un extrait du dernier message dans la liste
     * des conversations. Si le texte dépasse la longueur maximale, il est
     * coupé et des points de suspension sont ajoutés.
     * 
     * Exemple :
     * - Texte court : "Bonjour!" → "Bonjour!"
     * - Texte long : "Lorem ipsum dolor sit amet, consectetur..." → "Lorem ipsum dolor sit amet, consectetur..."
     * 
     * @param string $text Le texte à tronquer
     * @param int $length La longueur maximale désirée (en caractères)
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
     * Transformation :
     * - Input : "2024-11-04 15:43:27" (format MySQL DATETIME)
     * - Output : "15:43" (format HH:MM)
     * 
     * Utilisée pour :
     * - Affichage de l'heure du dernier message dans la liste des conversations
     * - Affichage de l'heure d'envoi de chaque message dans la conversation
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
     * pour un affichage compact dans l'interface de conversation.
     * 
     * Transformation :
     * - Input : "2024-11-04 15:43:27" (format MySQL DATETIME)
     * - Output : "04.11" (format JJ.MM)
     * 
     * Utilisée pour :
     * - Affichage de la date d'envoi de chaque message dans la conversation
     * - Séparateurs de date dans l'historique de conversation
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

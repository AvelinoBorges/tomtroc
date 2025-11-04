<?php
/**
 * Contrôleur de gestion de la messagerie
 * 
 * Ce contrôleur gère l'affichage de la messagerie et l'envoi de messages
 * entre les utilisateurs de la plateforme TomTroc.
 * Actuellement, il utilise des données fictives en attendant l'implémentation
 * complète avec la base de données.
 */

class MessageController extends Controller
{
    /**
     * Affiche la page de messagerie
     * 
     * Cette méthode affiche l'interface de messagerie avec la liste des conversations
     * et les messages de la conversation active. Pour le moment, les données sont
     * statiques et seront remplacées par des données réelles de la base de données.
     * 
     * @return void
     */
    public function index()
    {
        // Définir le titre de la page
        $pageTitle = 'Messagerie - Tom Troc';

        // Tableau de conversations fictives pour la démonstration
        // Chaque conversation représente un échange avec un utilisateur
        $conversations = [
            [
                'id' => 1,                                  // Identifiant unique de la conversation
                'username' => 'Alexlecture',                // Nom d'utilisateur du correspondant
                'avatar' => '/tomtroc/public/images/avatar-1.jpg',  // Photo de profil
                'lastMessage' => 'Lorem ipsum dolor sit amet, consectetur .adipiscing elit, sed do eiusmod tempor',  // Dernier message reçu
                'time' => '15:43',                          // Heure du dernier message
                'unread' => false                           // Indicateur de message non lu
            ],
            [
                'id' => 2,
                'username' => 'Nathalire',
                'avatar' => '/tomtroc/public/images/avatar-2.jpg',
                'lastMessage' => 'Lorem ipsum dolor sit amet, consectetur .adipiscing elit, sed do eiusmod tempor',
                'time' => '20.08',
                'unread' => false
            ],
            [
                'id' => 3,
                'username' => 'Sas634',
                'avatar' => '/tomtroc/public/images/avatar-3.jpg',
                'lastMessage' => 'Lorem ipsum dolor sit amet, consectetur .adipiscing elit, sed do eiusmod tempor',
                'time' => '15.08',
                'unread' => false
            ]
        ];

        // Tableau de messages de la conversation actuellement affichée
        // Distingue les messages envoyés ('me') des messages reçus ('other')
        $messages = [
            [
                'id' => 1,                                  // Identifiant unique du message
                'sender' => 'other',                        // Expéditeur : 'other' = correspondant, 'me' = utilisateur connecté
                'text' => 'Lorem ipsum dolor sit amet, consectetur .adipiscing elit, sed do eiusmod tempor',  // Contenu du message
                'time' => '21.08',                          // Date d'envoi
                'date' => '15:44'                           // Heure d'envoi
            ],
            [
                'id' => 2,
                'sender' => 'me',                           // Message envoyé par l'utilisateur connecté
                'text' => 'Lorem ipsum dolor sit amet, consectetur .adipiscing elit, sed do eiusmod tempor',
                'time' => '21.08',
                'date' => '15:48'
            ]
        ];

        // Rendre la vue de messagerie avec toutes les données nécessaires
        $this->render('messages/index', [
            'pageTitle' => $pageTitle,                      // Titre de la page
            'conversations' => $conversations,              // Liste des conversations
            'messages' => $messages,                        // Messages de la conversation active
            'activeUser' => 'Alexlecture',                  // Utilisateur de la conversation active
            'activeAvatar' => '/tomtroc/public/images/avatar-1.jpg'  // Avatar de l'utilisateur actif
        ]);
    }

    /**
     * Traite l'envoi d'un nouveau message
     * 
     * Cette méthode gère la soumission d'un nouveau message via le formulaire.
     * Pour le moment, elle redirige simplement vers la page de messagerie.
     * L'implémentation complète avec enregistrement en base de données
     * sera ajoutée ultérieurement.
     * 
     * @return void
     */
    public function send()
    {
        // TODO: Implémenter la logique d'enregistrement du message en base de données
        // - Récupérer les données POST (message, destinataire)
        // - Valider les données
        // - Enregistrer le message dans la table messages
        // - Gérer les erreurs éventuelles
        
        // Redirection vers la page de messagerie
        header('Location: /tomtroc/messages');
        exit;
    }
}

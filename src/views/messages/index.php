<!--
    Vue : Messagerie (Interface de chat)
    
    Cette page affiche l'interface complète de messagerie de TomTroc avec :
    - Une colonne gauche contenant la liste des conversations
    - Une colonne droite affichant les messages de la conversation active
    - Un formulaire d'envoi de nouveaux messages
    - Une interface responsive adaptée mobile (navigation entre liste et chat)
    
    Variables attendues depuis le contrôleur :
    - $pageTitle : Titre de la page
    - $conversations : Tableau des conversations de l'utilisateur
    - $messages : Tableau des messages de la conversation active
    - $activeUser : Pseudo de l'utilisateur avec qui on discute
    - $activeUserId : ID de l'utilisateur actif dans la conversation
    - $activeAvatar : Photo de profil de l'utilisateur actif
-->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Feuilles de style CSS -->
    <link rel="stylesheet" href="/tomtroc/public/css/fonts.css">
    <link rel="stylesheet" href="/tomtroc/public/css/style.css">
    <link rel="stylesheet" href="/tomtroc/public/css/header.css">
    <link rel="stylesheet" href="/tomtroc/public/css/footer.css">
    <link rel="stylesheet" href="/tomtroc/public/css/messages.css">
</head>
<body>
    <div class="wrapper">
        <!-- Inclusion du header (navigation principale) -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>

        <main class="messages-page">
            <!-- 
                Colonne gauche : Barre latérale des conversations
                Affiche la liste de toutes les conversations de l'utilisateur connecté
                Masquée sur mobile lorsqu'une conversation est ouverte
            -->
            <aside class="conversations-sidebar">
                <h1 class="messages-title">Messagerie</h1>
                
                <div class="conversations-list">
                    <?php if (empty($conversations)): ?>
                        <!-- Message affiché si l'utilisateur n'a aucune conversation -->
                        <div class="empty-conversations">
                            <p>Aucune conversation pour le moment.</p>
                            <p class="empty-subtitle">Commencez à échanger des livres pour discuter avec d'autres utilisateurs !</p>
                        </div>
                    <?php else: ?>
                        <!-- Boucle sur chaque conversation pour créer la liste -->
                        <?php foreach ($conversations as $conversation): ?>
                            <!-- 
                                Item de conversation : lien cliquable vers la conversation
                                Classe 'active' ajoutée si c'est la conversation actuellement affichée
                            -->
                            <a href="/tomtroc/messages?to=<?= $conversation['id'] ?>" class="conversation-item <?= isset($activeUserId) && $conversation['id'] == $activeUserId ? 'active' : '' ?>">
                                <!-- Avatar de l'utilisateur avec fallback en cas d'erreur -->
                                <div class="conversation-avatar">
                                    <img 
                                        src="<?= htmlspecialchars($conversation['avatar']) ?>" 
                                        alt="<?= htmlspecialchars($conversation['username']) ?>"
                                        onerror="this.src='/tomtroc/public/images/default-avatar.svg'"
                                    >
                                </div>
                                
                                <!-- Contenu de la conversation : nom, heure et aperçu du dernier message -->
                                <div class="conversation-content">
                                    <div class="conversation-header">
                                        <!-- Pseudo de l'utilisateur (protégé contre XSS) -->
                                        <span class="conversation-username"><?= htmlspecialchars($conversation['username']) ?></span>
                                        
                                        <!-- Heure du dernier message -->
                                        <span class="conversation-time"><?= htmlspecialchars($conversation['time']) ?></span>
                                    </div>
                                    
                                    <!-- Aperçu tronqué du dernier message -->
                                    <p class="conversation-preview"><?= htmlspecialchars($conversation['lastMessage']) ?></p>
                                </div>
                                
                                <!-- Badge de notification si messages non lus -->
                                <?php if ($conversation['unread']): ?>
                                    <span class="unread-badge"></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- 
                Colonne droite : Zone de chat principale
                Affiche les messages de la conversation active et le formulaire d'envoi
                Prend tout l'écran sur mobile lorsqu'une conversation est ouverte
            -->
            <div class="chat-area">
                <?php if ($activeUser): ?>
                    <!-- 
                        En-tête du chat (version mobile uniquement)
                        Affiche le bouton retour et les informations de l'utilisateur
                    -->
                    <div class="chat-header-mobile">
                        <!-- Bouton retour vers la liste des conversations (mobile) -->
                        <a href="/tomtroc/" class="back-button">
                            <svg width="8" height="14" viewBox="0 0 8 14" fill="none">
                                <path d="M7 1L1 7L7 13" stroke="#A6A6A6" stroke-width="1"/>
                            </svg>
                            <span>retour</span>
                        </a>
                        
                        <!-- Informations de l'utilisateur avec qui on discute -->
                        <div class="chat-user-info">
                            <img 
                                src="<?= htmlspecialchars($activeAvatar) ?>" 
                                alt="<?= htmlspecialchars($activeUser) ?>" 
                                class="chat-avatar"
                                onerror="this.src='/tomtroc/public/images/default-avatar.svg'"
                            >
                            <span class="chat-username"><?= htmlspecialchars($activeUser) ?></span>
                        </div>
                    </div>

                    <!-- 
                        Conteneur des messages : Zone scrollable affichant l'historique
                        ID utilisé par JavaScript pour le scroll automatique vers le bas
                    -->
                    <div class="messages-container" id="messagesContainer">
                        <?php if (empty($messages)): ?>
                            <!-- Message affiché si aucun message dans la conversation -->
                            <div class="empty-messages">
                                <p>Aucun message pour le moment.</p>
                                <p class="empty-subtitle">Envoyez le premier message pour démarrer la conversation !</p>
                            </div>
                        <?php else: ?>
                            <!-- Boucle sur chaque message de la conversation -->
                            <?php foreach ($messages as $message): ?>
                                <!-- 
                                    Bloc message : classe différente selon l'expéditeur
                                    - 'message-sent' : message envoyé par l'utilisateur (aligné à droite)
                                    - 'message-received' : message reçu (aligné à gauche)
                                -->
                                <div class="message <?= $message['sender'] === 'me' ? 'message-sent' : 'message-received' ?>">
                                    <!-- Avatar affiché uniquement pour les messages reçus -->
                                    <?php if ($message['sender'] === 'other'): ?>
                                        <img 
                                            src="<?= htmlspecialchars($activeAvatar) ?>" 
                                            alt="Avatar" 
                                            class="message-avatar"
                                            onerror="this.src='/tomtroc/public/images/default-avatar.svg'"
                                        >
                                    <?php endif; ?>
                                    
                                    <div class="message-content">
                                        <!-- 
                                            Bulle de message : contient le texte
                                            nl2br() convertit les sauts de ligne en <br> pour affichage correct
                                            htmlspecialchars() protège contre les attaques XSS
                                        -->
                                        <div class="message-bubble">
                                            <p><?= nl2br(htmlspecialchars($message['text'])) ?></p>
                                        </div>
                                        
                                        <!-- Horodatage : date et heure du message -->
                                        <div class="message-time">
                                            <span><?= htmlspecialchars($message['date']) ?></span>
                                            <span><?= htmlspecialchars($message['time']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- 
                        Zone de saisie : Formulaire d'envoi de message
                        Fixé en bas de la zone de chat
                    -->
                    <div class="message-input-area">
                        <form action="/tomtroc/messages/send" method="POST" class="message-form">
                            <!-- Champ caché : ID du destinataire pour traitement côté serveur -->
                            <input type="hidden" name="recipient_id" value="<?= htmlspecialchars($activeUserId) ?>">
                            
                            <!-- Champ de saisie du message (obligatoire) -->
                            <input 
                                type="text" 
                                name="message" 
                                class="message-input" 
                                placeholder="Tapez votre message ici"
                                required
                            >
                            
                            <!-- Bouton d'envoi -->
                            <button type="submit" class="send-button">Envoyer</button>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- 
                        État initial : Affiché lorsqu'aucune conversation n'est sélectionnée
                        Invite l'utilisateur à choisir ou démarrer une conversation
                    -->
                    <div class="no-active-chat">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <p>Sélectionnez une conversation pour commencer</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Inclusion du footer (pied de page) -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>

    <script>
        /**
         * Script de gestion de l'interface de messagerie
         * 
         * Fonctionnalités :
         * - Scroll automatique vers le dernier message
         * - Focus automatique sur le champ de saisie
         * - Navigation mobile entre liste de conversations et zone de chat
         * - Gestion du bouton retour mobile
         * - Responsive : adaptation selon la taille de l'écran
         */
        document.addEventListener('DOMContentLoaded', function() {
            // Récupération des éléments du DOM
            const messagesContainer = document.getElementById('messagesContainer');
            const messageInput = document.querySelector('.message-input');
            const messagesPage = document.querySelector('.messages-page');
            const backButton = document.querySelector('.back-button');
            const conversationItems = document.querySelectorAll('.conversation-item');
            
            /**
             * Auto-scroll vers le dernier message
             * Permet d'afficher automatiquement les messages les plus récents
             */
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            /**
             * Focus automatique sur le champ de saisie
             * Améliore l'expérience utilisateur en permettant de taper immédiatement
             */
            if (messageInput) {
                messageInput.focus();
            }

            /**
             * Gestion mobile : Afficher le chat lorsqu'une conversation est active
             * Sur mobile (≤768px), masque la liste et affiche le chat en plein écran
             */
            <?php if ($activeUser): ?>
            if (window.innerWidth <= 768) {
                messagesPage.classList.add('chat-active');
            }
            <?php endif; ?>

            /**
             * Bouton retour mobile : Retourner à la liste des conversations
             * Empêche la navigation par défaut et met à jour l'état de l'interface
             */
            if (backButton) {
                backButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    messagesPage.classList.remove('chat-active');
                    // Mise à jour de l'URL sans rechargement de la page (SPA behavior)
                    window.history.pushState({}, '', '/tomtroc/messages');
                });
            }

            /**
             * Clic sur une conversation (mobile) : Afficher le chat
             * Ajoute la classe 'chat-active' pour masquer la liste et afficher le chat
             */
            conversationItems.forEach(function(item) {
                item.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        messagesPage.classList.add('chat-active');
                    }
                });
            });

            /**
             * Gestion du redimensionnement de l'écran
             * Retire la classe 'chat-active' lors du passage en mode desktop
             * pour afficher simultanément la liste et le chat
             */
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    messagesPage.classList.remove('chat-active');
                }
            });
        });
    </script>
</body>
</html>

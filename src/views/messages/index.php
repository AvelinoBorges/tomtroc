<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Styles CSS -->
    <link rel="stylesheet" href="/tomtroc/public/css/fonts.css">
    <link rel="stylesheet" href="/tomtroc/public/css/style.css">
    <link rel="stylesheet" href="/tomtroc/public/css/header.css">
    <link rel="stylesheet" href="/tomtroc/public/css/footer.css">
    <link rel="stylesheet" href="/tomtroc/public/css/messages.css">
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>

        <main class="messages-page">
            <!-- Colonne gauche - Liste des conversations -->
            <aside class="conversations-sidebar">
                <h1 class="messages-title">Messagerie</h1>
                
                <div class="conversations-list">
                    <?php if (empty($conversations)): ?>
                        <div class="empty-conversations">
                            <p>Nenhuma conversa ainda.</p>
                            <p class="empty-subtitle">Comece a trocar livros para conversar com outros usuários!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conversation): ?>
                            <a href="/tomtroc/messages?to=<?= $conversation['id'] ?>" class="conversation-item <?= isset($activeUserId) && $conversation['id'] == $activeUserId ? 'active' : '' ?>">
                                <div class="conversation-avatar">
                                    <img 
                                        src="<?= htmlspecialchars($conversation['avatar']) ?>" 
                                        alt="<?= htmlspecialchars($conversation['username']) ?>"
                                        onerror="this.src='/tomtroc/public/images/default-avatar.svg'"
                                    >
                                </div>
                                <div class="conversation-content">
                                    <div class="conversation-header">
                                        <span class="conversation-username"><?= htmlspecialchars($conversation['username']) ?></span>
                                        <span class="conversation-time"><?= htmlspecialchars($conversation['time']) ?></span>
                                    </div>
                                    <p class="conversation-preview"><?= htmlspecialchars($conversation['lastMessage']) ?></p>
                                </div>
                                <?php if ($conversation['unread']): ?>
                                    <span class="unread-badge"></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Colonne droite - Zone de chat -->
            <div class="chat-area">
                <?php if ($activeUser): ?>
                    <!-- Header du chat mobile -->
                    <div class="chat-header-mobile">
                        <a href="/tomtroc/" class="back-button">
                            <svg width="8" height="14" viewBox="0 0 8 14" fill="none">
                                <path d="M7 1L1 7L7 13" stroke="#A6A6A6" stroke-width="1"/>
                            </svg>
                            <span>retour</span>
                        </a>
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

                    <!-- Zone des messages -->
                    <div class="messages-container" id="messagesContainer">
                        <?php if (empty($messages)): ?>
                            <div class="empty-messages">
                                <p>Nenhuma mensagem ainda.</p>
                                <p class="empty-subtitle">Envie a primeira mensagem para iniciar a conversa!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?= $message['sender'] === 'me' ? 'message-sent' : 'message-received' ?>">
                                    <?php if ($message['sender'] === 'other'): ?>
                                        <img 
                                            src="<?= htmlspecialchars($activeAvatar) ?>" 
                                            alt="Avatar" 
                                            class="message-avatar"
                                            onerror="this.src='/tomtroc/public/images/default-avatar.svg'"
                                        >
                                    <?php endif; ?>
                                    <div class="message-content">
                                        <div class="message-bubble">
                                            <p><?= nl2br(htmlspecialchars($message['text'])) ?></p>
                                        </div>
                                        <div class="message-time">
                                            <span><?= htmlspecialchars($message['date']) ?></span>
                                            <span><?= htmlspecialchars($message['time']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Zone de saisie -->
                    <div class="message-input-area">
                        <form action="/tomtroc/messages/send" method="POST" class="message-form">
                            <input type="hidden" name="recipient_id" value="<?= htmlspecialchars($activeUserId) ?>">
                            <input 
                                type="text" 
                                name="message" 
                                class="message-input" 
                                placeholder="Tapez votre message ici"
                                required
                            >
                            <button type="submit" class="send-button">Envoyer</button>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Message si pas de conversation active -->
                    <div class="no-active-chat">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                        <p>Sélectionnez une conversation pour commencer</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <!-- Footer -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>

    <script>
        // Auto-scroll para última mensagem
        document.addEventListener('DOMContentLoaded', function() {
            const messagesContainer = document.getElementById('messagesContainer');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Focus no input quando carregar a página
            const messageInput = document.querySelector('.message-input');
            if (messageInput) {
                messageInput.focus();
            }

            // Mobile: Controlar navegação entre lista e chat
            const messagesPage = document.querySelector('.messages-page');
            const backButton = document.querySelector('.back-button');
            
            // Se tem conversa ativa, mostrar o chat no mobile
            <?php if ($activeUser): ?>
            if (window.innerWidth <= 768) {
                messagesPage.classList.add('chat-active');
            }
            <?php endif; ?>

            // Botão de voltar no mobile
            if (backButton) {
                backButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    messagesPage.classList.remove('chat-active');
                    // Atualizar URL sem recarregar
                    window.history.pushState({}, '', '/tomtroc/messages');
                });
            }

            // Adicionar classe ao clicar em conversa (mobile)
            const conversationItems = document.querySelectorAll('.conversation-item');
            conversationItems.forEach(function(item) {
                item.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        messagesPage.classList.add('chat-active');
                    }
                });
            });

            // Remover classe chat-active ao redimensionar para desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    messagesPage.classList.remove('chat-active');
                }
            });
        });
    </script>
</body>
</html>

<!-- 
    Composant Header (Menu)
    Navigation principale de l'application TomTroc
-->
<?php
// Vérifier si l'utilisateur est connecté et compter les messages non lus
$isLoggedIn = isset($_SESSION['user']);
$unreadCount = 0;

if ($isLoggedIn) {
    $messageModel = new Message();
    $unreadCount = $messageModel->countUnread($_SESSION['user']['id']);
}
?>
<header class="header">
    <div class="header__container">
        <!-- Section gauche: Logo + Navigation -->
        <div class="header__left">
            <!-- Logo -->
            <a href="/tomtroc/" class="header__logo">
                <img src="/tomtroc/public/images/logo.svg" alt="Tom Troc">
            </a>
            
            <!-- Navigation principale -->
            <nav class="header__nav">
                <a href="/tomtroc/" class="header__nav-link">Accueil</a>
                <a href="/tomtroc/books" class="header__nav-link">Nos livres à l'échange</a>
            </nav>
        </div>
        
        <!-- Section droite -->
        <div class="header__right">
            <?php if ($isLoggedIn): ?>
                <!-- Messagerie com badge (apenas se logado) -->
                <a href="/tomtroc/messages" class="header__messagerie">
                    <img src="/tomtroc/public/images/icon-messagerie.svg" alt="" class="header__messagerie-icon">
                    <span class="header__messagerie-text">Messagerie</span>
                    <?php if ($unreadCount > 0): ?>
                        <div class="header__messagerie-badge">
                            <span class="header__messagerie-badge-text"><?= $unreadCount ?></span>
                        </div>
                    <?php endif; ?>
                </a>
                
                <!-- Mon compte -->
                <a href="/tomtroc/compte" class="header__compte">
                    <img src="/tomtroc/public/images/icon-compte.svg" alt="" class="header__compte-icon">
                    <span class="header__compte-text">Mon compte</span>
                </a>
                
                <!-- Déconnexion -->
                <a href="/tomtroc/auth/logout" class="header__connexion">Déconnexion</a>
            <?php else: ?>
                <!-- Connexion (apenas se não logado) -->
                <a href="/tomtroc/auth/login" class="header__connexion">Connexion</a>
            <?php endif; ?>
        </div>
        
        <!-- Menu mobile toggle -->
        <button class="header__menu-toggle" id="menuToggle" aria-label="Menu" aria-expanded="false">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>
    
    <!-- Menu mobile -->
    <nav class="header__mobile-menu" id="mobileMenu">
        <div class="header__mobile-menu-content">
            <a href="/tomtroc/" class="header__mobile-link">Accueil</a>
            <a href="/tomtroc/books" class="header__mobile-link">Nos livres à l'échange</a>
            
            <?php if ($isLoggedIn): ?>
                <a href="/tomtroc/messages" class="header__mobile-link">
                    Messagerie
                    <?php if ($unreadCount > 0): ?>
                        <span class="header__mobile-badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="/tomtroc/compte" class="header__mobile-link">Mon compte</a>
                <a href="/tomtroc/auth/logout" class="header__mobile-link header__mobile-link--logout">Déconnexion</a>
            <?php else: ?>
                <a href="/tomtroc/auth/login" class="header__mobile-link header__mobile-link--login">Connexion</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<script>
    // Menu mobile toggle
    (function() {
        const menuToggle = document.getElementById('menuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const body = document.body;
        
        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', function() {
                const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
                
                if (isOpen) {
                    // Fechar menu
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    body.style.overflow = '';
                } else {
                    // Abrir menu
                    menuToggle.setAttribute('aria-expanded', 'true');
                    menuToggle.classList.add('active');
                    mobileMenu.classList.add('active');
                    body.style.overflow = 'hidden';
                }
            });
            
            // Fechar menu ao clicar em um link
            const mobileLinks = mobileMenu.querySelectorAll('.header__mobile-link');
            mobileLinks.forEach(link => {
                link.addEventListener('click', function() {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    body.style.overflow = '';
                });
            });
            
            // Fechar menu ao pressionar ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && menuToggle.classList.contains('active')) {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    body.style.overflow = '';
                }
            });
        }
    })();
</script>

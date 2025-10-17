<!-- 
    Composant Header (Menu)
    Navigation principale de l'application TomTroc
-->
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
                <a href="/tomtroc/" class="header__nav-link header__nav-link--active">Accueil</a>
                <a href="/tomtroc/livres" class="header__nav-link">Nos livres à l'échange</a>
            </nav>
        </div>
        
        <!-- Section droite -->
        <div class="header__right">
            <!-- Messagerie avec badge -->
            <a href="/tomtroc/messagerie" class="header__messagerie">
                <img src="/tomtroc/public/images/icon-messagerie.svg" alt="" class="header__messagerie-icon">
                <span class="header__messagerie-text">Messagerie</span>
                <div class="header__messagerie-badge">
                    <span class="header__messagerie-badge-text">1</span>
                </div>
            </a>
            
            <!-- Mon compte -->
            <a href="/tomtroc/compte" class="header__compte">
                <img src="/tomtroc/public/images/icon-compte.svg" alt="" class="header__compte-icon">
                <span class="header__compte-text">Mon compte</span>
            </a>
            
            <!-- Connexion -->
            <a href="/tomtroc/connexion" class="header__connexion">Connexion</a>
        </div>
        
        <!-- Menu mobile toggle -->
        <button class="header__menu-toggle" aria-label="Menu">
            <img src="/tomtroc/public/images/icon-menu.svg" alt="">
        </button>
    </div>
</header>

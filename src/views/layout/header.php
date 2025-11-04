<!-- 
    Composant Header - En-tête et navigation principale
    
    Ce composant affiche l'en-tête de l'application TomTroc avec :
    - Le logo de l'application
    - La navigation principale (Accueil, Nos livres)
    - Les liens utilisateur (Messagerie, Mon compte, Connexion/Déconnexion)
    - Un badge de notification pour les messages non lus
    - Un menu responsive pour mobile avec navigation hamburger
    
    Le contenu varie selon l'état de connexion de l'utilisateur.
-->
<?php
/**
 * Initialisation des variables pour la gestion de l'état utilisateur
 * et du compteur de messages non lus
 */

// Vérifier si l'utilisateur est actuellement connecté via la session
$isLoggedIn = isset($_SESSION['user']);

// Initialiser le compteur de messages non lus
$unreadCount = 0;

// Si l'utilisateur est connecté, récupérer le nombre de messages non lus
if ($isLoggedIn) {
    $messageModel = new Message();
    // Compter les messages non lus pour afficher le badge de notification
    $unreadCount = $messageModel->countUnread($_SESSION['user']['id']);
}
?>
<header class="header">
    <div class="header__container">
        <!-- Section gauche: Logo + Navigation principale -->
        <div class="header__left">
            <!-- Logo de l'application - lien vers la page d'accueil -->
            <a href="/tomtroc/" class="header__logo">
                <img src="/tomtroc/public/images/logo.svg" alt="Tom Troc">
            </a>
            
            <!-- Navigation principale - liens vers les pages essentielles -->
            <nav class="header__nav">
                <a href="/tomtroc/" class="header__nav-link">Accueil</a>
                <a href="/tomtroc/books" class="header__nav-link">Nos livres à l'échange</a>
            </nav>
        </div>
        
        <!-- Section droite - Actions utilisateur -->
        <div class="header__right">
            <?php if ($isLoggedIn): ?>
                <!-- Lien vers la messagerie avec badge de notification (uniquement si connecté) -->
                <a href="/tomtroc/messages" class="header__messagerie">
                    <img src="/tomtroc/public/images/icon-messagerie.svg" alt="" class="header__messagerie-icon">
                    <span class="header__messagerie-text">Messagerie</span>
                    <?php if ($unreadCount > 0): ?>
                        <!-- Badge affichant le nombre de messages non lus -->
                        <div class="header__messagerie-badge">
                            <span class="header__messagerie-badge-text"><?= $unreadCount ?></span>
                        </div>
                    <?php endif; ?>
                </a>
                
                <!-- Lien vers la page Mon compte (gestion du profil et des livres) -->
                <a href="/tomtroc/compte" class="header__compte">
                    <img src="/tomtroc/public/images/icon-compte.svg" alt="" class="header__compte-icon">
                    <span class="header__compte-text">Mon compte</span>
                </a>
                
                <!-- Lien de déconnexion -->
                <a href="/tomtroc/auth/logout" class="header__connexion">Déconnexion</a>
            <?php else: ?>
                <!-- Lien de connexion (uniquement si non connecté) -->
                <a href="/tomtroc/auth/login" class="header__connexion">Connexion</a>
            <?php endif; ?>
        </div>
        
        <!-- Bouton hamburger pour le menu mobile -->
        <!-- Utilise les attributs ARIA pour l'accessibilité -->
        <button class="header__menu-toggle" id="menuToggle" aria-label="Menu" aria-expanded="false">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>
    
    <!-- Menu mobile - Navigation responsive pour petits écrans -->
    <!-- S'affiche en overlay complet lorsque le bouton hamburger est activé -->
    <nav class="header__mobile-menu" id="mobileMenu">
        <div class="header__mobile-menu-content">
            <!-- Liens de navigation principaux -->
            <a href="/tomtroc/" class="header__mobile-link">Accueil</a>
            <a href="/tomtroc/books" class="header__mobile-link">Nos livres à l'échange</a>
            
            <?php if ($isLoggedIn): ?>
                <!-- Liens utilisateur connecté - Version mobile -->
                <a href="/tomtroc/messages" class="header__mobile-link">
                    Messagerie
                    <?php if ($unreadCount > 0): ?>
                        <!-- Badge de notification pour les messages non lus -->
                        <span class="header__mobile-badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="/tomtroc/compte" class="header__mobile-link">Mon compte</a>
                <a href="/tomtroc/auth/logout" class="header__mobile-link header__mobile-link--logout">Déconnexion</a>
            <?php else: ?>
                <!-- Lien de connexion - Version mobile -->
                <a href="/tomtroc/auth/login" class="header__mobile-link header__mobile-link--login">Connexion</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<script>
    /**
     * Gestion du menu mobile - Toggle et interactions
     * 
     * Ce script gère l'ouverture et la fermeture du menu mobile avec :
     * - Toggle via le bouton hamburger
     * - Fermeture automatique lors du clic sur un lien
     * - Fermeture via la touche Échap pour l'accessibilité
     * - Gestion du scroll du body (bloqué quand menu ouvert)
     * - Attributs ARIA pour les lecteurs d'écran
     */
    (function() {
        // Récupérer les éléments du DOM nécessaires
        const menuToggle = document.getElementById('menuToggle');  // Bouton hamburger
        const mobileMenu = document.getElementById('mobileMenu');  // Menu mobile
        const body = document.body;                                // Body pour gérer le scroll
        
        // Vérifier que les éléments existent avant d'attacher les événements
        if (menuToggle && mobileMenu) {
            /**
             * Événement click sur le bouton hamburger
             * Permet d'ouvrir ou fermer le menu mobile
             */
            menuToggle.addEventListener('click', function() {
                // Vérifier l'état actuel du menu (ouvert ou fermé)
                const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';
                
                if (isOpen) {
                    // Fermer le menu
                    menuToggle.setAttribute('aria-expanded', 'false');  // Mise à jour ARIA
                    menuToggle.classList.remove('active');              // Animation du bouton
                    mobileMenu.classList.remove('active');              // Masquer le menu
                    body.style.overflow = '';                           // Restaurer le scroll
                } else {
                    // Ouvrir le menu
                    menuToggle.setAttribute('aria-expanded', 'true');   // Mise à jour ARIA
                    menuToggle.classList.add('active');                 // Animation du bouton
                    mobileMenu.classList.add('active');                 // Afficher le menu
                    body.style.overflow = 'hidden';                     // Bloquer le scroll de la page
                }
            });
            
            /**
             * Fermer le menu automatiquement lors du clic sur un lien
             * Améliore l'expérience utilisateur en évitant de laisser le menu ouvert
             */
            const mobileLinks = mobileMenu.querySelectorAll('.header__mobile-link');
            mobileLinks.forEach(link => {
                link.addEventListener('click', function() {
                    menuToggle.setAttribute('aria-expanded', 'false');
                    menuToggle.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    body.style.overflow = '';
                });
            });
            
            /**
             * Fermer le menu avec la touche Échap (Escape)
             * Fonctionnalité d'accessibilité standard pour les overlays/modaux
             */
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

<!--
    Vue : Détail d'un livre
    
    Cette page affiche toutes les informations détaillées d'un livre spécifique
    disponible sur la plateforme TomTroc.
    
    Fonctionnalités :
    - Affichage de l'image du livre (ou placeholder)
    - Titre, auteur et description complète
    - Informations sur le propriétaire avec lien vers son profil
    - Fil d'Ariane (breadcrumb) pour la navigation
    - Bouton d'action contextuel selon l'état de connexion et propriété
    
    Variables attendues depuis le contrôleur :
    - $pageTitle : Titre de la page
    - $book : Tableau avec toutes les données du livre (title, author, description, image, owner)
    - $isLoggedIn : Boolean indiquant si l'utilisateur est connecté
    - $isOwner : Boolean indiquant si l'utilisateur est le propriétaire du livre
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
    <link rel="stylesheet" href="/tomtroc/public/css/book.css">
</head>
<body>
    <div class="wrapper">
        <!-- Inclusion du header (navigation principale) -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>

<main class="book-page">
    <!-- 
        Fil d'Ariane (Breadcrumb) : Navigation hiérarchique
        Permet à l'utilisateur de comprendre où il se trouve et de naviguer facilement
    -->
    <div class="book-breadcrumb">
        <a href="/tomtroc/books" class="breadcrumb-link">Nos livres</a>
        <span class="breadcrumb-separator">></span>
        <span class="breadcrumb-current"><?= htmlspecialchars($book['title']) ?></span>
    </div>

    <!-- Contenu principal : Layout en deux colonnes -->
    <div class="book-content">
        
        <!-- Colonne gauche : Image du livre -->
        <div class="book-image-column">
            <div class="book-image-wrapper">
                <?php if (!empty($book['image']) && $book['image'] !== '/tomtroc/public/images/'): ?>
                    <!-- 
                        Image du livre si disponible
                        Protection XSS avec htmlspecialchars()
                    -->
                    <img 
                        src="<?= htmlspecialchars($book['image']) ?>" 
                        alt="<?= htmlspecialchars($book['title']) ?>" 
                        class="book-image"
                    >
                <?php else: ?>
                    <!-- 
                        Placeholder si aucune image n'est disponible
                        Affiche un message visuel par défaut
                    -->
                    <div class="book-no-image-detail">
                        <span>Aucune image</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Colonne droite : Informations détaillées du livre -->
        <div class="book-info-column">
            
            <!-- En-tête : Titre et auteur du livre -->
            <div class="book-header">
                <!-- Titre principal (protégé contre XSS) -->
                <h1 class="book-title"><?= htmlspecialchars($book['title']) ?></h1>
                
                <!-- Nom de l'auteur -->
                <p class="book-author">par <?= htmlspecialchars($book['author']) ?></p>
            </div>

            <!-- Séparateur visuel entre les sections -->
            <div class="book-separator"></div>

            <!-- Section Description : Texte descriptif du livre -->
            <div class="book-description-section">
                <h2 class="section-label">DESCRIPTION</h2>
                
                <!-- 
                    Description complète du livre
                    nl2br() convertit les sauts de ligne en <br> pour l'affichage formaté
                    htmlspecialchars() protège contre les attaques XSS
                -->
                <p class="book-description"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
            </div>

            <!-- 
                Section Propriétaire : Informations sur le possesseur du livre
                Lien cliquable vers le profil public du propriétaire
            -->
            <div class="book-owner-section">
                <h2 class="section-label">PROPRIÉTAIRE</h2>
                <a href="/tomtroc/user/profile/<?= $book['owner']['id'] ?>" class="book-owner">
                    <!-- 
                        Avatar du propriétaire avec fallback
                        onerror charge l'avatar par défaut si l'image échoue
                    -->
                    <div class="owner-avatar">
                        <img 
                            src="<?= htmlspecialchars($book['owner']['avatar']) ?>" 
                            alt="<?= htmlspecialchars($book['owner']['username']) ?>"
                            onerror="this.src='/tomtroc/public/images/default-avatar.svg'"
                        >
                    </div>
                    
                    <!-- Pseudo du propriétaire -->
                    <span class="owner-name"><?= htmlspecialchars($book['owner']['username']) ?></span>
                </a>
            </div>

            <!-- 
                Bouton Call-To-Action (CTA) : Action contextuelle selon l'utilisateur
                Le bouton change selon trois scénarios différents :
                1. Utilisateur connecté et non propriétaire : Envoyer un message
                2. Utilisateur propriétaire du livre : Modifier le livre
                3. Utilisateur non connecté : Invitation à se connecter
            -->
            <?php if (isset($isLoggedIn) && $isLoggedIn && !$isOwner): ?>
                <!-- 
                    Scénario 1 : Utilisateur connecté consultant le livre d'un autre utilisateur
                    Lien vers la messagerie avec paramètre 'to' pour démarrer une conversation
                -->
                <a href="/tomtroc/messages?to=<?= $book['owner']['id'] ?>" class="book-cta">Envoyer un message</a>
            
            <?php elseif (isset($isOwner) && $isOwner): ?>
                <!-- 
                    Scénario 2 : Utilisateur propriétaire consultant son propre livre
                    Bouton pour modifier les informations du livre
                -->
                <div class="book-owner-actions">
                    <a href="/tomtroc/compte/editBook/<?= $book['id'] ?>" class="book-cta book-cta--secondary">Modifier ce livre</a>
                </div>
            
            <?php else: ?>
                <!-- 
                    Scénario 3 : Utilisateur non connecté
                    Invitation à se connecter pour accéder à la messagerie
                -->
                <a href="/tomtroc/auth/login" class="book-cta">Connectez-vous pour contacter</a>
            <?php endif; ?>
        </div>
    </div>
</main>

        <!-- Inclusion du footer (pied de page) -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>
</body>
</html>

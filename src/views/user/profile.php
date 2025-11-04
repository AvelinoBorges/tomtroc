<!--
    Vue : Profil public d'un utilisateur
    
    Cette page affiche le profil public d'un utilisateur de TomTroc, comprenant :
    - Les informations du profil (photo, pseudo, ancienneté)
    - Le nombre de livres dans sa bibliothèque
    - Un bouton pour envoyer un message (si l'utilisateur n'est pas le propriétaire)
    - La liste complète des livres de l'utilisateur avec leurs détails
    
    Variables attendues depuis le contrôleur :
    - $pageTitle : Titre de la page
    - $user : Tableau avec les données de l'utilisateur
    - $profilePhoto : Chemin de la photo de profil
    - $memberSince : Ancienneté formatée (ex: "2 ans")
    - $bookCount : Nombre de livres de l'utilisateur
    - $books : Tableau des livres de l'utilisateur
    - $isLoggedIn : Boolean indiquant si le visiteur est connecté
    - $isOwnProfile : Boolean indiquant si c'est le propre profil du visiteur
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
    <link rel="stylesheet" href="/tomtroc/public/css/user-profile.css">
</head>
<body>
    <div class="wrapper">
        <!-- Inclusion du header (navigation principale) -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>

        <main class="profile-page">
            <div class="profile-container">
                <!-- Card de profil : Affiche les informations de l'utilisateur -->
                <div class="profile-card">
                    <div class="profile-info">
                        <!-- Photo de profil avec fallback en cas d'erreur de chargement -->
                        <div class="profile-avatar">
                            <img 
                                src="<?= htmlspecialchars($profilePhoto) ?>" 
                                alt="<?= htmlspecialchars($user['pseudo']) ?>"
                                onerror="this.src='/tomtroc/public/images/default-avatar.svg'"
                            >
                        </div>
                        <!-- Détails du profil : pseudo, ancienneté et statistiques de bibliothèque -->
                        <div class="profile-details">
                            <!-- Pseudo de l'utilisateur (protégé contre XSS) -->
                            <h2 class="profile-username"><?= htmlspecialchars($user['pseudo']) ?></h2>
                            
                            <!-- Ancienneté sur la plateforme -->
                            <p class="profile-member-since">Membre depuis <?= htmlspecialchars($memberSince) ?></p>
                            
                            <!-- Étiquette de section -->
                            <p class="profile-section-label">BIBLIOTHEQUE</p>
                            
                            <!-- Statistiques : nombre de livres avec pluralisation automatique -->
                            <div class="profile-stats">
                                <img src="/tomtroc/public/images/icon-book.svg" alt="Book icon" class="profile-book-icon">
                                <span class="profile-book-count"><?= $bookCount ?> livre<?= $bookCount > 1 ? 's' : '' ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 
                        Bouton d'action : Afficher uniquement si ce n'est pas le profil du visiteur
                        Le comportement varie selon l'état de connexion :
                        - Connecté : lien direct vers la messagerie avec l'utilisateur
                        - Non connecté : redirige vers la page de login
                    -->
                    <?php if (!$isOwnProfile): ?>
                        <?php if ($isLoggedIn): ?>
                            <!-- Si connecté : lien vers messagerie avec paramètre 'to' -->
                            <a href="/tomtroc/messages?to=<?= $user['id'] ?>" class="profile-cta">Écrire un message</a>
                        <?php else: ?>
                            <!-- Si non connecté : redirige vers login -->
                            <a href="/tomtroc/auth/login" class="profile-cta">Écrire un message</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Section bibliothèque : Liste de tous les livres de l'utilisateur -->
                <div class="profile-books-section">
                    <!-- En-tête du tableau des livres avec colonnes -->
                    <div class="books-header">
                        <div class="books-header-row">
                            <span class="books-header-col books-header-photo">PHOTO</span>
                            <span class="books-header-col books-header-title">TITRE</span>
                            <span class="books-header-col books-header-author">AUTEUR</span>
                            <span class="books-header-col books-header-description">DESCRIPTION</span>
                        </div>
                    </div>

                    <!-- Liste des livres -->
                    <div class="books-list">
                        <?php if (!empty($books)): ?>
                            <!-- Boucle sur chaque livre de l'utilisateur -->
                            <?php foreach ($books as $index => $book): ?>
                                <?php 
                                    // Définir l'image du livre (avec image par défaut si non définie)
                                    $bookImage = !empty($book['photo']) ? $book['photo'] : '/tomtroc/public/images/default-image.png';
                                    
                                    // Vérifier si c'est le dernier élément pour ne pas afficher le diviseur
                                    $isLastItem = $index === count($books) - 1;
                                ?>
                                <div class="book-item">
                                    <div class="book-row">
                                        <!-- Colonne photo : miniature du livre avec fallback -->
                                        <div class="book-col book-col-photo">
                                            <img 
                                                src="<?= htmlspecialchars($bookImage) ?>" 
                                                alt="<?= htmlspecialchars($book['titre']) ?>"
                                                class="book-thumbnail"
                                                onerror="this.src='/tomtroc/public/images/default-image.png'"
                                            >
                                        </div>
                                        
                                        <!-- Colonne titre : lien cliquable vers la page détaillée du livre -->
                                        <div class="book-col book-col-title">
                                            <a href="/tomtroc/book/show/<?= $book['id'] ?>" class="book-title-link">
                                                <?= htmlspecialchars($book['titre']) ?>
                                            </a>
                                        </div>
                                        
                                        <!-- Colonne auteur : nom de l'auteur (protégé contre XSS) -->
                                        <div class="book-col book-col-author">
                                            <?= htmlspecialchars($book['auteur']) ?>
                                        </div>
                                        
                                        <!-- 
                                            Colonne description : aperçu tronqué à 150 caractères
                                            Utilise mb_substr pour gérer correctement les caractères multioctets (accents)
                                            Ajoute '...' si la description est plus longue
                                        -->
                                        <div class="book-col book-col-description">
                                            <?= htmlspecialchars(mb_substr($book['description'], 0, 150)) ?><?= mb_strlen($book['description']) > 150 ? '...' : '' ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Diviseur entre les livres (sauf pour le dernier) -->
                                    <?php if (!$isLastItem): ?>
                                        <div class="book-divider"></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Message affiché si l'utilisateur n'a aucun livre -->
                            <div class="no-books">
                                <p>Cet utilisateur n'a pas encore ajouté de livres.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Diviseur visuel en bas de la page pour séparer le contenu du footer -->
            <div class="profile-bottom-divider"></div>
        </main>

        <!-- Inclusion du footer (pied de page) -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>
</body>
</html>

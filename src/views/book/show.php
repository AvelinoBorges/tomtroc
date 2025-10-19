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
    <link rel="stylesheet" href="/tomtroc/public/css/book.css">
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>

<main class="book-page">
    <!-- Breadcrumb -->
    <div class="book-breadcrumb">
        <a href="/tomtroc/books" class="breadcrumb-link">Nos livres</a>
        <span class="breadcrumb-separator">></span>
        <span class="breadcrumb-current"><?= htmlspecialchars($book['title']) ?></span>
    </div>

    <!-- Contenu principal -->
    <div class="book-content">
        <!-- Colonne gauche : Image -->
        <div class="book-image-column">
            <div class="book-image-wrapper">
                <?php if (!empty($book['image']) && $book['image'] !== '/tomtroc/public/images/'): ?>
                    <img 
                        src="<?= htmlspecialchars($book['image']) ?>" 
                        alt="<?= htmlspecialchars($book['title']) ?>" 
                        class="book-image"
                    >
                <?php else: ?>
                    <div class="book-no-image-detail">
                        <span>Aucune image</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Colonne droite : Informations -->
        <div class="book-info-column">
            <!-- Titre et auteur -->
            <div class="book-header">
                <h1 class="book-title"><?= htmlspecialchars($book['title']) ?></h1>
                <p class="book-author">par <?= htmlspecialchars($book['author']) ?></p>
            </div>

            <!-- Séparateur -->
            <div class="book-separator"></div>

            <!-- Description -->
            <div class="book-description-section">
                <h2 class="section-label">DESCRIPTION</h2>
                <p class="book-description"><?= nl2br(htmlspecialchars($book['description'])) ?></p>
            </div>

            <!-- Propriétaire -->
            <div class="book-owner-section">
                <h2 class="section-label">PROPRIÉTAIRE</h2>
                <a href="/tomtroc/user/profile/<?= $book['owner']['id'] ?>" class="book-owner">
                    <div class="owner-avatar">
                        <img 
                            src="<?= htmlspecialchars($book['owner']['avatar']) ?>" 
                            alt="<?= htmlspecialchars($book['owner']['username']) ?>"
                            onerror="this.src='/tomtroc/public/images/default-avatar.svg'"
                        >
                    </div>
                    <span class="owner-name"><?= htmlspecialchars($book['owner']['username']) ?></span>
                </a>
            </div>

            <!-- Bouton CTA -->
            <?php if (isset($isLoggedIn) && $isLoggedIn && !$isOwner): ?>
                <a href="/tomtroc/messages?to=<?= $book['owner']['id'] ?>" class="book-cta">Envoyer un message</a>
            <?php elseif (isset($isOwner) && $isOwner): ?>
                <div class="book-owner-actions">
                    <a href="/tomtroc/compte/editBook/<?= $book['id'] ?>" class="book-cta book-cta--secondary">Modifier ce livre</a>
                </div>
            <?php else: ?>
                <a href="/tomtroc/auth/login" class="book-cta">Connectez-vous pour contacter</a>
            <?php endif; ?>
        </div>
    </div>
</main>

        <!-- Footer -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>
</body>
</html>

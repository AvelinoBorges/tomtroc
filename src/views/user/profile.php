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
    <link rel="stylesheet" href="/tomtroc/public/css/user-profile.css">
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>

        <main class="profile-page">
            <div class="profile-container">
                <!-- Card de profil de l'utilisateur -->
                <div class="profile-card">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <img 
                                src="<?= htmlspecialchars($profilePhoto) ?>" 
                                alt="<?= htmlspecialchars($user['pseudo']) ?>"
                                onerror="this.src='/tomtroc/public/images/default-avatar.svg'"
                            >
                        </div>
                        <div class="profile-details">
                            <h2 class="profile-username"><?= htmlspecialchars($user['pseudo']) ?></h2>
                            <p class="profile-member-since">Membre depuis <?= htmlspecialchars($memberSince) ?></p>
                            <p class="profile-section-label">BIBLIOTHEQUE</p>
                            <div class="profile-stats">
                                <img src="/tomtroc/public/images/icon-book.svg" alt="Book icon" class="profile-book-icon">
                                <span class="profile-book-count"><?= $bookCount ?> livre<?= $bookCount > 1 ? 's' : '' ?></span>
                            </div>
                        </div>
                    </div>
                    <?php if (!$isOwnProfile): ?>
                        <?php if ($isLoggedIn): ?>
                            <a href="/tomtroc/messages?to=<?= $user['id'] ?>" class="profile-cta">Écrire un message</a>
                        <?php else: ?>
                            <a href="/tomtroc/auth/login" class="profile-cta">Écrire un message</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Section des livres -->
                <div class="profile-books-section">
                    <div class="books-header">
                        <div class="books-header-row">
                            <span class="books-header-col books-header-photo">PHOTO</span>
                            <span class="books-header-col books-header-title">TITRE</span>
                            <span class="books-header-col books-header-author">AUTEUR</span>
                            <span class="books-header-col books-header-description">DESCRIPTION</span>
                        </div>
                    </div>

                    <div class="books-list">
                        <?php if (!empty($books)): ?>
                            <?php foreach ($books as $index => $book): ?>
                                <?php 
                                    $bookImage = !empty($book['photo']) ? $book['photo'] : '/tomtroc/public/images/default-image.png';
                                    $isLastItem = $index === count($books) - 1;
                                ?>
                                <div class="book-item">
                                    <div class="book-row">
                                        <div class="book-col book-col-photo">
                                            <img 
                                                src="<?= htmlspecialchars($bookImage) ?>" 
                                                alt="<?= htmlspecialchars($book['titre']) ?>"
                                                class="book-thumbnail"
                                                onerror="this.src='/tomtroc/public/images/default-image.png'"
                                            >
                                        </div>
                                        <div class="book-col book-col-title">
                                            <a href="/tomtroc/book/show/<?= $book['id'] ?>" class="book-title-link">
                                                <?= htmlspecialchars($book['titre']) ?>
                                            </a>
                                        </div>
                                        <div class="book-col book-col-author">
                                            <?= htmlspecialchars($book['auteur']) ?>
                                        </div>
                                        <div class="book-col book-col-description">
                                            <?= htmlspecialchars(mb_substr($book['description'], 0, 150)) ?><?= mb_strlen($book['description']) > 150 ? '...' : '' ?>
                                        </div>
                                    </div>
                                    <?php if (!$isLastItem): ?>
                                        <div class="book-divider"></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-books">
                                <p>Cet utilisateur n'a pas encore ajouté de livres.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Divider final -->
            <div class="profile-bottom-divider"></div>
        </main>

        <!-- Footer -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>
</body>
</html>

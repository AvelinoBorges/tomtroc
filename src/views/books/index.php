<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Nos livres'; ?> - TomTroc</title>
    
    <!-- Styles CSS -->
    <link rel="stylesheet" href="/tomtroc/public/css/fonts.css">
    <link rel="stylesheet" href="/tomtroc/public/css/style.css">
    <link rel="stylesheet" href="/tomtroc/public/css/header.css">
    <link rel="stylesheet" href="/tomtroc/public/css/footer.css">
    <link rel="stylesheet" href="/tomtroc/public/css/books.css">
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>
        
        <!-- Contenu principal -->
        <main class="books-page">
            <!-- Section titre et recherche -->
            <section class="books-header">
                <div class="books-header__container">
                    <h1 class="books-header__title"><?= htmlspecialchars($pageTitle) ?></h1>
                    
                    <form action="/tomtroc/books" method="GET" class="search-bar-form">
                        <div class="search-bar">
                            <button type="submit" class="search-bar__icon-button" title="Rechercher">
                                <svg class="search-bar__icon" width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="6.5" cy="6.5" r="5.75" stroke="#A6A6A6" stroke-width="1.5"/>
                                    <line x1="10.9393" y1="10.0607" x2="15.0607" y2="14.182" stroke="#A6A6A6" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                            <input 
                                type="text" 
                                name="search"
                                id="searchInput"
                                class="search-bar__input" 
                                placeholder="Rechercher un livre"
                                value="<?= htmlspecialchars($searchTerm ?? '') ?>"
                                aria-label="Rechercher un livre"
                            >
                            <?php if (!empty($searchTerm)): ?>
                                <button type="button" id="clearSearch" class="search-bar__clear" title="Effacer la recherche">
                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1L13 13M1 13L13 1" stroke="#A6A6A6" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Section grille de livres -->
            <section class="books-grid-section">
                <?php if (empty($books)): ?>
                    <div class="no-books-message">
                        <p>Aucun livre trouvé<?= !empty($searchTerm) ? ' pour "' . htmlspecialchars($searchTerm) . '"' : '' ?>.</p>
                        <?php if (!empty($searchTerm)): ?>
                            <a href="/tomtroc/books" class="btn-reset-search">Voir tous les livres</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="books-grid">
                        <?php foreach ($books as $book): ?>
                            <a href="/tomtroc/book/show/<?= $book['id'] ?>" class="book-card <?= !$book['available'] ? 'book-card--unavailable' : '' ?>">
                                <?php if (!$book['available']): ?>
                                    <span class="book-card__badge">non dispo.</span>
                                <?php endif; ?>
                                
                                <div class="book-card__image">
                                    <?php 
                                    // Vérifie si l'image a déjà le chemin complet ou s'il faut l'ajouter
                                    $imagePath = $book['image'];
                                    if ($imagePath === 'default-book.png' || $imagePath === 'default-image.png') {
                                        $imageUrl = '/tomtroc/public/images/default-image.png';
                                    } elseif (strpos($imagePath, 'books/') === 0) {
                                        $imageUrl = '/tomtroc/public/images/' . $imagePath;
                                    } else {
                                        $imageUrl = '/tomtroc/public/images/' . $imagePath;
                                    }
                                    ?>
                                    <img 
                                        src="<?= htmlspecialchars($imageUrl) ?>" 
                                        alt="<?= htmlspecialchars($book['title']) ?>"
                                        onerror="this.src='/tomtroc/public/images/default-image.png'"
                                    >
                                </div>
                                <div class="book-card__content">
                                    <h3 class="book-card__title"><?= htmlspecialchars($book['title']) ?></h3>
                                    <p class="book-card__author"><?= htmlspecialchars($book['author']) ?></p>
                                    <div class="book-card__seller">
                                        <span>Vendu par :</span>
                                        <span class="book-card__username"><?= htmlspecialchars($book['seller']) ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
        
        <!-- Footer -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>

    <script>
        // Funcionalidade de limpar pesquisa
        const clearButton = document.getElementById('clearSearch');
        const searchInput = document.getElementById('searchInput');

        if (clearButton) {
            clearButton.addEventListener('click', function() {
                window.location.href = '/tomtroc/books';
            });
        }

        // Auto-submit ao pressionar Enter
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.closest('form').submit();
                }
            });

            // Focus no campo de pesquisa ao carregar (se houver termo)
            <?php if (!empty($searchTerm)): ?>
                searchInput.focus();
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            <?php endif; ?>
        }
    </script>
</body>
</html>

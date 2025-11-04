<!--
    Vue : Liste des livres (Nos livres à l'échange)
    
    Cette page affiche la liste complète de tous les livres disponibles
    pour l'échange sur la plateforme TomTroc.
    
    Fonctionnalités :
    - Affichage en grille de tous les livres
    - Barre de recherche pour filtrer les livres
    - Badge visuel pour les livres non disponibles
    - Lien vers la page de détail de chaque livre
    - État vide avec message si aucun résultat
    
    Variables attendues depuis le contrôleur :
    - $pageTitle : Titre de la page
    - $books : Tableau des livres à afficher
    - $searchTerm : Terme de recherche (optionnel)
-->
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
        
        <!-- Contenu principal : Page de liste des livres -->
        <main class="books-page">
            <!-- Section en-tête : Titre et barre de recherche -->
            <section class="books-header">
                <div class="books-header__container">
                    <!-- Titre principal de la page (protégé contre XSS) -->
                    <h1 class="books-header__title"><?= htmlspecialchars($pageTitle) ?></h1>
                    
                    <!--
                        Formulaire de recherche
                        Méthode GET pour permettre les liens partageables et le retour en arrière
                    -->
                    <form action="/tomtroc/books" method="GET" class="search-bar-form">
                        <div class="search-bar">
                            <!-- Bouton de soumission avec icône de loupe -->
                            <button type="submit" class="search-bar__icon-button" title="Rechercher">
                                <svg class="search-bar__icon" width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="6.5" cy="6.5" r="5.75" stroke="#A6A6A6" stroke-width="1.5"/>
                                    <line x1="10.9393" y1="10.0607" x2="15.0607" y2="14.182" stroke="#A6A6A6" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </button>
                            
                            <!--
                                Champ de recherche
                                Pré-rempli avec le terme de recherche si une recherche est active
                                aria-label pour l'accessibilité
                            -->
                            <input 
                                type="text" 
                                name="search"
                                id="searchInput"
                                class="search-bar__input" 
                                placeholder="Rechercher un livre"
                                value="<?= htmlspecialchars($searchTerm ?? '') ?>"
                                aria-label="Rechercher un livre"
                            >
                            
                            <!--
                                Bouton d'effacement de la recherche
                                Affiché uniquement lorsqu'un terme de recherche est actif
                            -->
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

            <!-- Section grille : Affichage des livres ou message d'état vide -->
            <section class="books-grid-section">
                <?php if (empty($books)): ?>
                    <!--
                        État vide : Aucun livre trouvé
                        Message adapté selon qu'une recherche est active ou non
                    -->
                    <div class="no-books-message">
                        <p>Aucun livre trouvé<?= !empty($searchTerm) ? ' pour "' . htmlspecialchars($searchTerm) . '"' : '' ?>.</p>
                        <?php if (!empty($searchTerm)): ?>
                            <!-- Bouton pour revenir à la liste complète -->
                            <a href="/tomtroc/books" class="btn-reset-search">Voir tous les livres</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Grille de livres : Layout responsive en colonnes -->
                    <div class="books-grid">
                        <!-- Boucle sur chaque livre -->
                        <?php foreach ($books as $book): ?>
                            <!--
                                Card de livre : Lien cliquable vers la page de détail
                                Classe additionnelle si le livre est non disponible
                            -->
                            <a href="/tomtroc/book/show/<?= $book['id'] ?>" class="book-card <?= !$book['available'] ? 'book-card--unavailable' : '' ?>">
                                
                                <!-- Badge "non dispo." affiché uniquement si le livre n'est pas disponible -->
                                <?php if (!$book['available']): ?>
                                    <span class="book-card__badge">non dispo.</span>
                                <?php endif; ?>
                                
                                <!-- Section image du livre -->
                                <div class="book-card__image">
                                    <?php 
                                    /**
                                     * Construction du chemin de l'image
                                     * Gestion de différents cas :
                                     * - Images par défaut (default-book.png, default-image.png)
                                     * - Chemins commençant déjà par 'books/'
                                     * - Chemins nécessitant l'ajout du préfixe complet
                                     */
                                    $imagePath = $book['image'];
                                    if ($imagePath === 'default-book.png' || $imagePath === 'default-image.png') {
                                        $imageUrl = '/tomtroc/public/images/default-image.png';
                                    } elseif (strpos($imagePath, 'books/') === 0) {
                                        $imageUrl = '/tomtroc/public/images/' . $imagePath;
                                    } else {
                                        $imageUrl = '/tomtroc/public/images/' . $imagePath;
                                    }
                                    ?>
                                    <!--
                                        Image du livre avec fallback
                                        onerror charge l'image par défaut si l'image principale échoue
                                    -->
                                    <img 
                                        src="<?= htmlspecialchars($imageUrl) ?>" 
                                        alt="<?= htmlspecialchars($book['title']) ?>"
                                        onerror="this.src='/tomtroc/public/images/default-image.png'"
                                    >
                                </div>
                                
                                <!-- Section contenu : Informations textuelles du livre -->
                                <div class="book-card__content">
                                    <!-- Titre du livre -->
                                    <h3 class="book-card__title"><?= htmlspecialchars($book['title']) ?></h3>
                                    
                                    <!-- Nom de l'auteur -->
                                    <p class="book-card__author"><?= htmlspecialchars($book['author']) ?></p>
                                    
                                    <!-- Informations du vendeur -->
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
        
        <!-- Inclusion du footer (pied de page) -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>

    <!--
        Script JavaScript : Gestion de la barre de recherche
        
        Fonctionnalités :
        - Bouton d'effacement de la recherche
        - Soumission automatique du formulaire avec Entrée
        - Focus automatique sur le champ si une recherche est active
        - Positionnement du curseur en fin de texte
    -->
    <script>
        // Récupération des éléments du DOM
        const clearButton = document.getElementById('clearSearch');
        const searchInput = document.getElementById('searchInput');

        /**
         * Fonctionnalité : Effacer la recherche
         * Redirige vers la page sans paramètres de recherche
         */
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                window.location.href = '/tomtroc/books';
            });
        }

        if (searchInput) {
            /**
             * Fonctionnalité : Soumission avec la touche Entrée
             * Empêche le comportement par défaut et soumet le formulaire
             */
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.closest('form').submit();
                }
            });

            /**
             * Fonctionnalité : Focus automatique sur le champ de recherche
             * Activé uniquement si un terme de recherche existe déjà
             * Positionne le curseur à la fin du texte pour faciliter la modification
             */
            <?php if (!empty($searchTerm)): ?>
                searchInput.focus();
                // Place le curseur à la fin du texte
                searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
            <?php endif; ?>
        }
    </script>
</body>
</html>

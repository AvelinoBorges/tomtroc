<?php
/**
 * Vue de la page d'accueil de TomTroc
 * 
 * Cette page présente la plateforme d'échange de livres avec :
 * - Une section hero avec un appel à l'action
 * - Les derniers livres ajoutés à la plateforme
 * - Une explication du fonctionnement de l'échange
 * - Les valeurs de l'association
 * 
 * Variables attendues :
 * @var array $latestBooks Tableau contenant les derniers livres ajoutés (titre, auteur, image, vendeur)
 * 
 * @package TomTroc
 * @author Équipe TomTroc
 * @version 1.0
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TomTroc - Plateforme d'échange de livres</title>
    
    <!-- Styles CSS -->
    <link rel="stylesheet" href="/tomtroc/public/css/fonts.css">
    <link rel="stylesheet" href="/tomtroc/public/css/style.css">
    <link rel="stylesheet" href="/tomtroc/public/css/header.css">
    <link rel="stylesheet" href="/tomtroc/public/css/footer.css">
    <link rel="stylesheet" href="/tomtroc/public/css/home.css">
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>
        
        <!-- Contenu principal de la page d'accueil -->
        <main class="home">
            <!-- 
                Section Hero 
                Bannière principale présentant la plateforme et son objectif
            -->
            <section class="hero">
                <!-- Contenu textuel du hero -->
                <div class="hero__content">
                    <h1 class="hero__title">Rejoignez nos lecteurs passionnés</h1>
                    <p class="hero__description">Donnez une nouvelle vie à vos livres en les échangeant avec d'autres amoureux de la lecture. Nous croyons en la magie du partage de connaissances et d'histoires à travers les livres.</p>
                    <!-- Bouton d'appel à l'action vers la page des livres -->
                    <a href="/tomtroc/books" class="btn btn--primary btn--hero">Découvrir</a>
                </div>
                <!-- Image illustrative de la section hero -->
                <div class="hero__image">
                    <img src="/tomtroc/public/images/hero-image.png" alt="Livres et lecture">
                </div>
            </section>

            <!-- 
                Section Derniers livres 
                Affiche les 4 derniers livres ajoutés à la plateforme
            -->
            <section class="latest-books">
                <h2 class="section-title">Les derniers livres ajoutés</h2>
                
                <!-- Grille des cartes de livres -->
                <div class="books-grid">
                    <?php if (!empty($latestBooks)): ?>
                        <?php 
                        /**
                         * Boucle sur chaque livre récent
                         * Affiche une carte cliquable pour chaque livre avec son image, titre, auteur et vendeur
                         */
                        foreach ($latestBooks as $book): 
                        ?>
                            <!-- Carte de livre cliquable menant vers la page de détails -->
                            <a href="/tomtroc/book/show/<?= $book['id'] ?>" class="book-card">
                                <!-- Image du livre -->
                                <div class="book-card__image">
                                    <?php if (!empty($book['image']) && $book['image'] !== '/tomtroc/public/images/'): ?>
                                        <!-- Affichage de l'image si elle existe -->
                                        <img 
                                            src="<?= htmlspecialchars($book['image']) ?>" 
                                            alt="<?= htmlspecialchars($book['title']) ?>"
                                        >
                                    <?php else: ?>
                                        <!-- Placeholder si aucune image n'est disponible -->
                                        <div class="book-no-image-home">
                                            <span>Aucune image</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!-- Informations du livre -->
                                <div class="book-card__content">
                                    <h3 class="book-card__title"><?= htmlspecialchars($book['title']) ?></h3>
                                    <p class="book-card__author"><?= htmlspecialchars($book['author']) ?></p>
                                    <!-- Informations sur le vendeur -->
                                    <div class="book-card__seller">
                                        <span>Vendu par :</span>
                                        <span class="book-card__username"><?= htmlspecialchars($book['seller']) ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Message affiché si aucun livre n'est disponible -->
                        <p class="no-books-message">Aucun livre disponible pour le moment.</p>
                    <?php endif; ?>
                </div>

                <!-- Bouton pour accéder à la liste complète des livres -->
                <a href="/tomtroc/books" class="btn btn--primary btn--center">Voir tous les livres</a>
            </section>

            <!-- 
                Section Comment ça marche 
                Explique le processus d'échange en 4 étapes simples
            -->
            <section class="how-it-works">
                <h2 class="section-title">Comment ça marche ?</h2>
                <p class="how-it-works__intro">Échanger des livres avec TomTroc c'est simple et amusant ! Suivez ces étapes pour commencer :</p>
                
                <!-- Grille des étapes du processus d'échange -->
                <div class="steps-grid">
                    <!-- Étape 1 : Inscription -->
                    <div class="step-card">
                        <p>Inscrivez-vous gratuitement sur notre plateforme.</p>
                    </div>
                    <!-- Étape 2 : Ajout de livres -->
                    <div class="step-card">
                        <p>Ajoutez les livres que vous souhaitez échanger à votre profil.</p>
                    </div>
                    <!-- Étape 3 : Navigation -->
                    <div class="step-card">
                        <p>Parcourez les livres disponibles chez d'autres membres.</p>
                    </div>
                    <!-- Étape 4 : Échange -->
                    <div class="step-card">
                        <p>Proposez un échange et discutez avec d'autres passionnés de lecture.</p>
                    </div>
                </div>

                <!-- Bouton d'accès à tous les livres -->
                <a href="/tomtroc/books" class="btn btn--outline btn--center">Voir tous les livres</a>
            </section>

            <!-- Bannière décorative de séparation -->
            <div class="banner-decoration">
                <img src="/tomtroc/public/images/mask-decoration.png" alt="Décoration">
            </div>

            <!-- 
                Section Nos valeurs 
                Présente la philosophie et les valeurs de l'association TomTroc
            -->
            <section class="our-values">
                <!-- Contenu textuel des valeurs -->
                <div class="our-values__content">
                    <h2 class="section-title">Nos valeurs</h2>
                    <!-- Texte descriptif des valeurs de l'association -->
                    <div class="our-values__text">
                        <p>Chez Tom Troc, nous mettons l'accent sur le partage, la découverte et la communauté. Nos valeurs sont ancrées dans notre passion pour les livres et notre désir de créer des liens entre les lecteurs. Nous croyons en la puissance des histoires pour rassembler les gens et inspirer des conversations enrichissantes.</p>
                        <p>Notre association a été fondée avec une conviction profonde : chaque livre mérite d'être lu et partagé.</p>
                        <p>Nous sommes passionnés par la création d'une plateforme conviviale qui permet aux lecteurs de se connecter, de partager leurs découvertes littéraires et d'échanger des livres qui attendent patiemment sur les étagères.</p>
                    </div>
                    <!-- Signature de l'équipe -->
                    <p class="our-values__signature">L'équipe Tom Troc</p>
                </div>
                <!-- Élément décoratif visuel -->
                <div class="our-values__decoration">
                    <img src="/tomtroc/public/images/vector-decoration.svg" alt="">
                </div>
            </section>
        </main>
        
        <!-- Footer -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>
</body>
</html>

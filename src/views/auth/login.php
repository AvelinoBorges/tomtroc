<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Titre de la page avec échappement HTML pour la sécurité -->
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Styles CSS -->
    <!-- Police personnalisée pour l'ensemble du site -->
    <link rel="stylesheet" href="/tomtroc/public/css/fonts.css">
    <!-- Styles globaux de base -->
    <link rel="stylesheet" href="/tomtroc/public/css/style.css">
    <!-- Styles spécifiques à l'en-tête -->
    <link rel="stylesheet" href="/tomtroc/public/css/header.css">
    <!-- Styles spécifiques au pied de page -->
    <link rel="stylesheet" href="/tomtroc/public/css/footer.css">
    <!-- Styles spécifiques aux pages d'authentification -->
    <link rel="stylesheet" href="/tomtroc/public/css/auth.css">
</head>
<body>
    <!-- Conteneur principal de la page -->
    <div class="wrapper">
        <!-- Header -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>

        <!-- Contenu principal de la page de connexion -->
        <main class="auth-page">
            <!-- Conteneur de la page d'authentification avec layout deux colonnes -->
            <div class="auth-page-container">
                <!-- Colonne gauche - Formulaire de connexion -->
                <div class="auth-form-column">
                    <!-- Titre principal de la page -->
                    <h1 class="auth-title">Connexion</h1>

                    <!-- Affichage des messages d'erreur si présents -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-error">
                            <!-- Échappement HTML pour éviter les injections XSS -->
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Affichage des messages de succès si présents -->
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <!-- Échappement HTML pour éviter les injections XSS -->
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire de connexion - Envoie les données en POST vers processLogin -->
                    <form action="/tomtroc/auth/processLogin" method="POST" class="auth-form">
                        <!-- Champ Email -->
                        <div class="form-group">
                            <!-- Label pour l'accessibilité -->
                            <label for="email" class="form-label">Adresse email</label>
                            <!-- Input email avec validation HTML5 -->
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                required
                                placeholder="Adresse email"
                            >
                        </div>

                        <!-- Champ Mot de passe -->
                        <div class="form-group">
                            <!-- Label pour l'accessibilité -->
                            <label for="password" class="form-label">Mot de passe</label>
                            <!-- Input password masqué -->
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                required
                                placeholder="Mot de passe"
                            >
                        </div>

                        <!-- Bouton de soumission du formulaire -->
                        <button type="submit" class="auth-submit-btn">Se connecter</button>

                        <!-- Lien vers la page d'inscription pour les nouveaux utilisateurs -->
                        <p class="auth-footer-text">
                            Pas de compte ? <a href="/tomtroc/auth/register" class="auth-link">Inscrivez-vous</a>
                        </p>
                    </form>
                </div>

                <!-- Colonne droite - Image décorative -->
                <div class="auth-image-column">
                    <!-- Wrapper pour le contrôle du style de l'image -->
                    <div class="auth-image-wrapper">
                        <!-- Image illustrative de livres pour la page d'authentification -->
                        <img src="<?= '/tomtroc/public/images/auth-image.png' ?>" alt="Livres" class="auth-image">
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>
</body>
</html>

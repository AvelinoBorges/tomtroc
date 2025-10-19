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
    <link rel="stylesheet" href="/tomtroc/public/css/auth.css">
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>

        <main class="auth-page">
            <div class="auth-page-container">
                <!-- Colonne gauche - Formulaire -->
                <div class="auth-form-column">
                    <h1 class="auth-title">Inscription</h1>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-error">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form action="/tomtroc/auth/processRegister" method="POST" class="auth-form">
                        <!-- Pseudo -->
                        <div class="form-group">
                            <label for="pseudo" class="form-label">Pseudo</label>
                            <input 
                                type="text" 
                                id="pseudo" 
                                name="pseudo" 
                                class="form-input" 
                                required
                                placeholder="Pseudo"
                            >
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label for="email" class="form-label">Adresse email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                required
                                placeholder="Adresse email"
                            >
                        </div>

                        <!-- Mot de passe -->
                        <div class="form-group">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input" 
                                required
                                placeholder="Mot de passe"
                            >
                        </div>

                        <!-- Confirmation du mot de passe -->
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirmation du mot de passe</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                required
                                placeholder="Confirmez votre mot de passe"
                            >
                        </div>

                        <!-- Bouton d'inscription -->
                        <button type="submit" class="auth-submit-btn">S'inscrire</button>

                        <!-- Lien de connexion -->
                        <p class="auth-footer-text">
                            Déjà inscrit ? <a href="/tomtroc/auth/login" class="auth-link">Connectez-vous</a>
                        </p>
                    </form>
                </div>

                <!-- Colonne droite - Image -->
                <div class="auth-image-column">
                    <div class="auth-image-wrapper">
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

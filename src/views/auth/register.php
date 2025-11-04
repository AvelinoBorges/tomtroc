<!--
/**
 * Vue d'inscription (Register)
 * 
 * Cette page permet aux nouveaux utilisateurs de créer un compte sur TomTroc.
 * Le formulaire collecte le pseudo, l'email, le mot de passe et sa confirmation,
 * puis les envoie au AuthController pour validation et création de compte.
 * 
 * Variables attendues:
 * @var string $pageTitle - Titre de la page (généralement "Inscription")
 * @var string $error (optionnel) - Message d'erreur (ex: email déjà utilisé, mots de passe différents)
 * @var string $success (optionnel) - Message de succès après inscription réussie
 * 
 * Layout:
 * - Colonne gauche: Formulaire d'inscription (pseudo, email, mot de passe, confirmation)
 * - Colonne droite: Image décorative de livres
 * 
 * Validations:
 * - Pseudo: Requis
 * - Email: Format valide requis (validation HTML5)
 * - Mot de passe: Minimum 6 caractères (validé côté serveur)
 * - Confirmation: Doit correspondre au mot de passe
 * 
 * Sécurité:
 * - Échappement HTML avec htmlspecialchars() pour prévenir les XSS
 * - Hachage bcrypt du mot de passe côté serveur (voir User model)
 * - Validation HTML5 des champs (type email, required)
 * - Transmission POST des données sensibles
 * 
 * @author TomTroc
 * @version 1.0
 */
-->
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

        <!-- Contenu principal de la page d'inscription -->
        <main class="auth-page">
            <!-- Conteneur de la page d'authentification avec layout deux colonnes -->
            <div class="auth-page-container">
                <!-- Colonne gauche - Formulaire d'inscription -->
                <div class="auth-form-column">
                    <!-- Titre principal de la page -->
                    <h1 class="auth-title">Inscription</h1>

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

                    <!-- Formulaire d'inscription - Envoie les données en POST vers processRegister -->
                    <form action="/tomtroc/auth/processRegister" method="POST" class="auth-form">
                        <!-- Champ Pseudo -->
                        <div class="form-group">
                            <!-- Label pour l'accessibilité -->
                            <label for="pseudo" class="form-label">Pseudo</label>
                            <!-- Input texte pour le nom d'utilisateur -->
                            <input 
                                type="text" 
                                id="pseudo" 
                                name="pseudo" 
                                class="form-input" 
                                required
                                placeholder="Pseudo"
                            >
                        </div>

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

                        <!-- Champ Confirmation du mot de passe -->
                        <div class="form-group">
                            <!-- Label pour l'accessibilité -->
                            <label for="confirm_password" class="form-label">Confirmation du mot de passe</label>
                            <!-- Input password pour vérifier la correspondance des mots de passe -->
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input" 
                                required
                                placeholder="Confirmez votre mot de passe"
                            >
                        </div>

                        <!-- Bouton de soumission du formulaire -->
                        <button type="submit" class="auth-submit-btn">S'inscrire</button>

                        <!-- Lien vers la page de connexion pour les utilisateurs existants -->
                        <p class="auth-footer-text">
                            Déjà inscrit ? <a href="/tomtroc/auth/login" class="auth-link">Connectez-vous</a>
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

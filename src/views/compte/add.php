<!--
    Vue : Ajouter un livre
    
    Cette page permet à l'utilisateur connecté d'ajouter un nouveau livre
    à sa bibliothèque personnelle pour le partage et l'échange.
    
    Fonctionnalités :
    - Upload d'une photo du livre (optionnel)
    - Saisie du titre et de l'auteur (obligatoires)
    - Ajout d'une description (optionnelle)
    - Définition de la disponibilité pour l'échange
    
    Variables attendues depuis le contrôleur :
    - $pageTitle : Titre de la page
    - $error : Message d'erreur (optionnel)
-->
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
    <link rel="stylesheet" href="/tomtroc/public/css/compte.css">
</head>
<body>
    <div class="wrapper">
        <!-- Inclusion du header (navigation principale) -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>

        <main class="add-book-page">
            <div class="add-book-container">
                <h1 class="add-book-title">Ajouter un livre</h1>

                <!-- Affichage du message d'erreur si présent -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!--
                    Formulaire d'ajout de livre
                    enctype="multipart/form-data" : nécessaire pour l'upload de fichiers
                    Traité par la méthode processAdd du CompteController
                -->
                <form action="/tomtroc/compte/processAdd" method="POST" enctype="multipart/form-data" class="add-book-form">
                    
                    <!-- Champ Photo : Upload d'image (optionnel) -->
                    <div class="form-group">
                        <label for="photo" class="form-label">Photo du livre</label>
                        <input 
                            type="file" 
                            id="photo" 
                            name="photo" 
                            accept="image/*"
                            class="form-input"
                        >
                        <!-- Texte d'aide indiquant les contraintes du fichier -->
                        <small class="form-help">Formats acceptés : JPG, PNG, GIF (Max 5MB)</small>
                    </div>

                    <!-- Champ Titre : Obligatoire -->
                    <div class="form-group">
                        <label for="titre" class="form-label">Titre *</label>
                        <input 
                            type="text" 
                            id="titre" 
                            name="titre" 
                            class="form-input" 
                            required
                            placeholder="Titre du livre"
                        >
                    </div>

                    <!-- Champ Auteur : Obligatoire -->
                    <div class="form-group">
                        <label for="auteur" class="form-label">Auteur *</label>
                        <input 
                            type="text" 
                            id="auteur" 
                            name="auteur" 
                            class="form-input" 
                            required
                            placeholder="Nom de l'auteur"
                        >
                    </div>

                    <!-- Champ Description : Optionnel, zone de texte multiligne -->
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-textarea" 
                            rows="8"
                            placeholder="Description du livre..."
                        ></textarea>
                    </div>

                    <!-- 
                        Checkbox Disponibilité : Indique si le livre est disponible pour l'échange
                        Coché par défaut (checked) pour que le livre soit disponible dès l'ajout
                    -->
                    <div class="form-group-checkbox">
                        <label class="checkbox-label">
                            <input 
                                type="checkbox" 
                                name="disponible" 
                                checked
                            >
                            <span>Livre disponible pour l'échange</span>
                        </label>
                    </div>

                    <!-- Boutons d'action : Soumettre ou annuler -->
                    <div class="form-actions">
                        <!-- Bouton principal : Soumet le formulaire -->
                        <button type="submit" class="btn-primary">Ajouter le livre</button>
                        
                        <!-- Lien secondaire : Retour à la page Mon compte sans enregistrer -->
                        <a href="/tomtroc/compte" class="btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </main>

        <!-- Inclusion du footer (pied de page) -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>
</body>
</html>

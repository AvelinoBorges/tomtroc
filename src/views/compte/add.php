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
        <!-- Header -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'header.php'; ?>

        <main class="add-book-page">
            <div class="add-book-container">
                <h1 class="add-book-title">Ajouter un livre</h1>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="/tomtroc/compte/processAdd" method="POST" enctype="multipart/form-data" class="add-book-form">
                    <!-- Photo du livre -->
                    <div class="form-group">
                        <label for="photo" class="form-label">Photo du livre</label>
                        <input 
                            type="file" 
                            id="photo" 
                            name="photo" 
                            accept="image/*"
                            class="form-input"
                        >
                        <small class="form-help">Formats acceptés : JPG, PNG, GIF (Max 5MB)</small>
                    </div>

                    <!-- Titre -->
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

                    <!-- Auteur -->
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

                    <!-- Description -->
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

                    <!-- Disponibilité -->
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

                    <!-- Boutons -->
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Ajouter le livre</button>
                        <a href="/tomtroc/compte" class="btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </main>

        <!-- Footer -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>
</body>
</html>

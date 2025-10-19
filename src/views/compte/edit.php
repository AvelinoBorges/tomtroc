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

        <main class="edit-book-page">
            <div class="edit-book-container">
                <!-- Lien retour -->
                <a href="/tomtroc/compte" class="back-link">
                    <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 1L1 7L7 13" stroke="#A6A6A6" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>retour</span>
                </a>

                <!-- Titre -->
                <h1 class="edit-book-title">Modifier les informations</h1>

                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Formulaire -->
                <form action="/tomtroc/compte/processEdit/<?= $book['id'] ?>" method="POST" enctype="multipart/form-data" class="edit-book-form">
                    <div class="form-content">
                        <!-- Colonne gauche : Photo -->
                        <div class="form-photo-section">
                            <div class="photo-upload-container">
                                <div class="photo-label-text">Photo</div>
                                <div class="photo-preview">
                                    <?php if (!empty($book['photo'])): ?>
                                        <img src="/tomtroc/public/images/<?= htmlspecialchars($book['photo']) ?>" alt="Photo du livre" id="bookPhotoPreview">
                                    <?php else: ?>
                                        <img src="/tomtroc/public/images/book-example.png" alt="Photo du livre" id="bookPhotoPreview">
                                    <?php endif; ?>
                                </div>
                                <label for="photo" class="photo-upload-label">
                                    <span>Modifier la photo</span>
                                </label>
                                <input 
                                    type="file" 
                                    id="photo" 
                                    name="photo" 
                                    accept="image/*"
                                    class="photo-input-hidden"
                                >
                            </div>
                        </div>

                        <!-- Colonne droite : Champs -->
                        <div class="form-fields-section">
                            <!-- Titre -->
                            <div class="form-group-edit">
                                <label for="titre" class="form-label-edit">Titre</label>
                                <input 
                                    type="text" 
                                    id="titre" 
                                    name="titre" 
                                    class="form-input-edit" 
                                    required
                                    value="<?= htmlspecialchars($book['titre']) ?>"
                                >
                            </div>

                            <!-- Auteur -->
                            <div class="form-group-edit">
                                <label for="auteur" class="form-label-edit">Auteur</label>
                                <input 
                                    type="text" 
                                    id="auteur" 
                                    name="auteur" 
                                    class="form-input-edit" 
                                    required
                                    value="<?= htmlspecialchars($book['auteur']) ?>"
                                >
                            </div>

                            <!-- Commentaire -->
                            <div class="form-group-edit">
                                <label for="description" class="form-label-edit">Commentaire</label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    class="form-textarea-edit"
                                ><?= htmlspecialchars($book['description'] ?? '') ?></textarea>
                            </div>

                            <!-- Disponibilité -->
                            <div class="form-group-edit">
                                <label for="disponible" class="form-label-edit">Disponibilité</label>
                                <div class="select-wrapper">
                                    <select 
                                        id="disponible" 
                                        name="disponible" 
                                        class="form-select-edit"
                                    >
                                        <option value="1" <?= $book['disponible'] ? 'selected' : '' ?>>disponible</option>
                                        <option value="0" <?= !$book['disponible'] ? 'selected' : '' ?>>non disponible</option>
                                    </select>
                                    <svg class="select-arrow" width="14" height="7" viewBox="0 0 14 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1L7 6L13 1" stroke="#000000" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Bouton Valider -->
                            <button type="submit" class="btn-validate">Valider</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>

        <!-- Footer -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>

    <script>
        // Preview da foto quando selecionada
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('bookPhotoPreview').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>

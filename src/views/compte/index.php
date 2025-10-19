<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon compte - TomTroc</title>
    
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
        
        <!-- Contenu principal -->
        <main class="compte-page">
            <h1 class="compte-title">Mon compte</h1>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="compte-content">
                <!-- Section Profil -->
                <div class="profile-section">
                    <div class="profile-card">
                        <div class="profile-info">
                            <div class="profile-photo-container">
                                <div class="profile-photo">
                                    <img id="profile-photo-preview" src="<?php echo htmlspecialchars($user['photoUrl']); ?>" alt="Photo de profil">
                                </div>
                                <label for="profile-photo-upload" class="profile-photo-modify">modifier</label>
                                <input type="file" id="profile-photo-upload" accept="image/*" style="display: none;">
                            </div>
                            <div class="profile-details">
                                <h2 class="profile-name"><?php echo htmlspecialchars($user['pseudo']); ?></h2>
                                <p class="profile-member-since"><?php echo htmlspecialchars($user['memberSince']); ?></p>
                                <p class="profile-library-label">BIBLIOTHEQUE</p>
                                <div class="profile-book-count">
                                    <img src="/tomtroc/public/images/icon-book.svg" alt="Book icon" width="11" height="14">
                                    <span><?php echo htmlspecialchars($user['bookCount']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire d'informations -->
                    <div class="info-form-card">
                        <h3 class="info-form-title">Vos informations personnelles</h3>
                        <form method="POST" action="/tomtroc/compte/updateProfile" enctype="multipart/form-data" class="profile-form" id="profile-form">
                            <!-- Campo escondido para foto de perfil -->
                            <input type="file" name="photo" id="profile-photo-input" accept="image/*" style="display: none;">
                            
                            <div class="form-group-compte">
                                <label>Adresse email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-input-compte" required>
                            </div>
                            <div class="form-group-compte">
                                <label>Mot de passe</label>
                                <input type="password" name="new_password" placeholder="Nouveau mot de passe (laisser vide pour ne pas changer)" class="form-input-compte">
                            </div>
                            <div class="form-group-compte">
                                <label>Pseudo</label>
                                <input type="text" name="pseudo" value="<?php echo htmlspecialchars($user['pseudo']); ?>" class="form-input-compte" required>
                            </div>
                            <button type="submit" class="btn-save-compte">Enregistrer</button>
                        </form>
                    </div>
                </div>

                <!-- Section Bibliothèque -->
                <div class="library-section">
                    <div class="library-header">
                        <div class="library-header-item">PHOTO</div>
                        <div class="library-header-item">TITRE</div>
                        <div class="library-header-item">AUTEUR</div>
                        <div class="library-header-item">DESCRIPTION</div>
                        <div class="library-header-item">DISPONIBILITE</div>
                        <div class="library-header-item">ACTION</div>
                    </div>

                    <?php if (empty($books)): ?>
                        <div class="no-books-message">
                            <p>Vous n'avez pas encore de livres dans votre bibliothèque.</p>
                            <a href="/tomtroc/compte/add" class="btn-add-book">Ajouter un livre</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
                    <div class="book-item">
                        <div class="book-item-photo">
                            <?php if (!empty($book['photo'])): ?>
                                <img src="/tomtroc/public/images/<?php echo htmlspecialchars($book['photo']); ?>" alt="<?php echo htmlspecialchars($book['titre']); ?>">
                            <?php else: ?>
                                <div class="book-no-image">
                                    <span>Aucune image</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="book-item-title"><?php echo htmlspecialchars($book['titre']); ?></div>
                        <div class="book-item-author"><?php echo htmlspecialchars($book['auteur']); ?></div>
                        <div class="book-item-description"><?php echo nl2br(htmlspecialchars($book['description'] ?? '')); ?></div>
                        <div class="book-item-availability">
                            <span class="tag-availability <?php echo $book['disponible'] ? 'tag-available' : 'tag-unavailable'; ?>">
                                <?php echo $book['disponible'] ? 'disponible' : 'non dispo.'; ?>
                            </span>
                        </div>
                        <div class="book-item-actions">
                            <a href="/tomtroc/compte/editBook/<?php echo $book['id']; ?>" class="action-link">Éditer</a>
                            <a href="/tomtroc/compte/deleteBook/<?php echo $book['id']; ?>" class="action-link action-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livre ?');">Supprimer</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>

    <!-- JavaScript para preview da foto de perfil -->
    <script>
        // Quando clicar em "modifier", abre o seletor de arquivo
        document.getElementById('profile-photo-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validar tipo de arquivo
                if (!file.type.startsWith('image/')) {
                    alert('Veuillez sélectionner une image valide.');
                    return;
                }
                
                // Validar tamanho (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('L\'image ne doit pas dépasser 5MB.');
                    return;
                }
                
                // Preview da imagem
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('profile-photo-preview').src = event.target.result;
                };
                reader.readAsDataURL(file);
                
                // Copiar o arquivo para o input do formulário
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('profile-photo-input').files = dataTransfer.files;
                
                // Mostrar mensagem de que a foto foi selecionada
                const modifyText = document.querySelector('.profile-photo-modify');
                modifyText.textContent = 'Photo sélectionnée - cliquez sur Enregistrer';
                modifyText.style.color = '#00AC66';
            }
        });
    </script>
</body>
</html>

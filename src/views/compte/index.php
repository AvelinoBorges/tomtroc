<!--
    Vue : Mon compte (Gestion du profil et de la bibliothèque)
    
    Cette page permet à l'utilisateur connecté de :
    - Visualiser et modifier ses informations personnelles (email, pseudo, mot de passe)
    - Gérer sa photo de profil
    - Consulter et gérer sa bibliothèque de livres
    - Ajouter, modifier ou supprimer des livres
    
    Variables attendues depuis le contrôleur :
    - $user : Tableau avec les données de l'utilisateur (pseudo, email, photo, ancienneté, nombre de livres)
    - $books : Tableau des livres de l'utilisateur
    - $success : Message de succès (optionnel)
    - $error : Message d'erreur (optionnel)
-->
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
        
        <!-- Contenu principal : Page de gestion du compte utilisateur -->
        <main class="compte-page">
            <h1 class="compte-title">Mon compte</h1>

            <!-- Affichage des messages de feedback -->
            <?php if (isset($success)): ?>
                <!-- Message de succès (confirmation d'action réussie) -->
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <!-- Message d'erreur (action échouée ou validation) -->
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="compte-content">
                <!-- Section Profil : Card d'informations utilisateur et formulaire de modification -->
                <div class="profile-section">
                    <!-- Card de profil : Affiche photo, pseudo, ancienneté et statistiques -->
                    <div class="profile-card">
                        <div class="profile-info">
                            <!-- Photo de profil avec possibilité de modification -->
                            <div class="profile-photo-container">
                                <div class="profile-photo">
                                    <!-- Image de profil avec preview dynamique -->
                                    <img id="profile-photo-preview" src="<?php echo htmlspecialchars($user['photoUrl']); ?>" alt="Photo de profil">
                                </div>
                                <!-- Label cliquable pour ouvrir le sélecteur de fichier -->
                                <label for="profile-photo-upload" class="profile-photo-modify">modifier</label>
                                <!-- Input file caché (déclenché par le label) -->
                                <input type="file" id="profile-photo-upload" accept="image/*" style="display: none;">
                            </div>
                            
                            <!-- Détails du profil : informations affichées -->
                            <div class="profile-details">
                                <!-- Pseudo de l'utilisateur -->
                                <h2 class="profile-name"><?php echo htmlspecialchars($user['pseudo']); ?></h2>
                                
                                <!-- Ancienneté sur la plateforme -->
                                <p class="profile-member-since"><?php echo htmlspecialchars($user['memberSince']); ?></p>
                                
                                <!-- Étiquette de section -->
                                <p class="profile-library-label">BIBLIOTHEQUE</p>
                                
                                <!-- Nombre de livres dans la bibliothèque -->
                                <div class="profile-book-count">
                                    <img src="/tomtroc/public/images/icon-book.svg" alt="Book icon" width="11" height="14">
                                    <span><?php echo htmlspecialchars($user['bookCount']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulaire de modification des informations personnelles -->
                    <div class="info-form-card">
                        <h3 class="info-form-title">Vos informations personnelles</h3>
                        
                        <!-- 
                            Formulaire de mise à jour du profil
                            enctype="multipart/form-data" : nécessaire pour l'upload de la photo
                        -->
                        <form method="POST" action="/tomtroc/compte/updateProfile" enctype="multipart/form-data" class="profile-form" id="profile-form">
                            <!-- 
                                Input caché pour la photo de profil
                                Rempli par JavaScript lorsque l'utilisateur sélectionne une photo
                            -->
                            <input type="file" name="photo" id="profile-photo-input" accept="image/*" style="display: none;">
                            
                            <!-- Champ Email : obligatoire -->
                            <div class="form-group-compte">
                                <label>Adresse email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-input-compte" required>
                            </div>
                            
                            <!-- 
                                Champ Mot de passe : optionnel
                                Si laissé vide, le mot de passe actuel n'est pas modifié
                            -->
                            <div class="form-group-compte">
                                <label>Mot de passe</label>
                                <input type="password" name="new_password" placeholder="Nouveau mot de passe (laisser vide pour ne pas changer)" class="form-input-compte">
                            </div>
                            
                            <!-- Champ Pseudo : obligatoire -->
                            <div class="form-group-compte">
                                <label>Pseudo</label>
                                <input type="text" name="pseudo" value="<?php echo htmlspecialchars($user['pseudo']); ?>" class="form-input-compte" required>
                            </div>
                            
                            <!-- Bouton de soumission du formulaire -->
                            <button type="submit" class="btn-save-compte">Enregistrer</button>
                        </form>
                    </div>
                </div>

                <!-- Section Bibliothèque : Liste des livres de l'utilisateur -->
                <div class="library-section">
                    <!-- En-tête du tableau avec les colonnes -->
                    <div class="library-header">
                        <div class="library-header-item">PHOTO</div>
                        <div class="library-header-item">TITRE</div>
                        <div class="library-header-item">AUTEUR</div>
                        <div class="library-header-item">DESCRIPTION</div>
                        <div class="library-header-item">DISPONIBILITE</div>
                        <div class="library-header-item">ACTION</div>
                    </div>

                    <?php if (empty($books)): ?>
                        <!-- Message affiché si l'utilisateur n'a aucun livre -->
                        <div class="no-books-message">
                            <p>Vous n'avez pas encore de livres dans votre bibliothèque.</p>
                            <a href="/tomtroc/compte/add" class="btn-add-book">Ajouter un livre</a>
                        </div>
                    <?php else: ?>
                        <!-- Boucle sur chaque livre de l'utilisateur -->
                        <?php foreach ($books as $book): ?>
                    <!-- Item de livre : une ligne du tableau -->
                    <div class="book-item">
                        <!-- Colonne Photo : affiche l'image ou un placeholder -->
                        <div class="book-item-photo">
                            <?php if (!empty($book['photo'])): ?>
                                <img src="/tomtroc/public/images/<?php echo htmlspecialchars($book['photo']); ?>" alt="<?php echo htmlspecialchars($book['titre']); ?>">
                            <?php else: ?>
                                <!-- Placeholder si pas d'image -->
                                <div class="book-no-image">
                                    <span>Aucune image</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Colonne Titre : nom du livre -->
                        <div class="book-item-title"><?php echo htmlspecialchars($book['titre']); ?></div>
                        
                        <!-- Colonne Auteur : nom de l'auteur -->
                        <div class="book-item-author"><?php echo htmlspecialchars($book['auteur']); ?></div>
                        
                        <!-- 
                            Colonne Description : texte de description
                            nl2br() convertit les sauts de ligne en <br> pour l'affichage
                        -->
                        <div class="book-item-description"><?php echo nl2br(htmlspecialchars($book['description'] ?? '')); ?></div>
                        
                        <!-- 
                            Colonne Disponibilité : tag coloré indiquant le statut
                            Classe CSS différente selon disponibilité (vert ou gris)
                        -->
                        <div class="book-item-availability">
                            <span class="tag-availability <?php echo $book['disponible'] ? 'tag-available' : 'tag-unavailable'; ?>">
                                <?php echo $book['disponible'] ? 'disponible' : 'non dispo.'; ?>
                            </span>
                        </div>
                        
                        <!-- 
                            Colonne Actions : liens pour modifier ou supprimer
                            Le lien Supprimer affiche une confirmation JavaScript
                        -->
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

        <!-- Inclusion du footer (pied de page) -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>

    <!--
        Script JavaScript : Gestion du changement de photo de profil
        
        Fonctionnalités :
        - Preview instantané de la photo sélectionnée
        - Validation du type de fichier (images uniquement)
        - Validation de la taille (max 5MB)
        - Transfert du fichier vers le formulaire principal
        - Feedback visuel après sélection
    -->
    <script>
        /**
         * Gestionnaire d'événement pour le changement de photo de profil
         * Déclenché lorsque l'utilisateur sélectionne un fichier via le label "modifier"
         */
        document.getElementById('profile-photo-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                /**
                 * Validation 1 : Type de fichier
                 * Vérifie que le fichier est bien une image
                 */
                if (!file.type.startsWith('image/')) {
                    alert('Veuillez sélectionner une image valide.');
                    return;
                }
                
                /**
                 * Validation 2 : Taille du fichier
                 * Limite à 5MB pour éviter les uploads trop lourds
                 */
                if (file.size > 5 * 1024 * 1024) {
                    alert('L\'image ne doit pas dépasser 5MB.');
                    return;
                }
                
                /**
                 * Preview de l'image : Affichage instantané
                 * Utilise FileReader pour lire le fichier et l'afficher
                 */
                const reader = new FileReader();
                reader.onload = function(event) {
                    // Remplace la photo actuelle par la nouvelle
                    document.getElementById('profile-photo-preview').src = event.target.result;
                };
                reader.readAsDataURL(file);
                
                /**
                 * Transfert du fichier vers l'input caché du formulaire
                 * Utilise DataTransfer API pour copier le fichier
                 */
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('profile-photo-input').files = dataTransfer.files;
                
                /**
                 * Feedback visuel : Modifier le texte et la couleur du label
                 * Indique à l'utilisateur que la photo est prête à être enregistrée
                 */
                const modifyText = document.querySelector('.profile-photo-modify');
                modifyText.textContent = 'Photo sélectionnée - cliquez sur Enregistrer';
                modifyText.style.color = '#00AC66';
            }
        });
    </script>
</body>
</html>

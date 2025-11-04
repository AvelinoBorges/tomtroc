<!--
    Vue : Modifier un livre
    
    Cette page permet à l'utilisateur de modifier les informations d'un livre
    existant dans sa bibliothèque personnelle.
    
    Fonctionnalités :
    - Modification de la photo du livre avec preview instantané
    - Modification du titre et de l'auteur
    - Modification de la description
    - Changement de la disponibilité (disponible/non disponible)
    
    Variables attendues depuis le contrôleur :
    - $pageTitle : Titre de la page
    - $book : Tableau avec toutes les données du livre à modifier
    - $error : Message d'erreur (optionnel)
-->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Feuilles de style CSS -->
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

        <main class="edit-book-page">
            <div class="edit-book-container">
                <!-- Lien de retour vers la page Mon compte -->
                <a href="/tomtroc/compte" class="back-link">
                    <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 1L1 7L7 13" stroke="#A6A6A6" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>retour</span>
                </a>

                <!-- Titre de la page -->
                <h1 class="edit-book-title">Modifier les informations</h1>

                <!-- Affichage du message d'erreur si présent -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!--
                    Formulaire de modification de livre
                    enctype="multipart/form-data" : nécessaire pour l'upload de fichiers
                    L'action inclut l'ID du livre pour le traitement côté serveur
                -->
                <form action="/tomtroc/compte/processEdit/<?= $book['id'] ?>" method="POST" enctype="multipart/form-data" class="edit-book-form">
                    <div class="form-content">
                        
                        <!-- Colonne gauche : Section photo du livre -->
                        <div class="form-photo-section">
                            <div class="photo-upload-container">
                                <div class="photo-label-text">Photo</div>
                                
                                <!-- 
                                    Zone de preview de la photo
                                    Affiche la photo actuelle ou une image par défaut
                                    ID utilisé par JavaScript pour mise à jour dynamique
                                -->
                                <div class="photo-preview">
                                    <?php if (!empty($book['photo'])): ?>
                                        <img src="/tomtroc/public/images/<?= htmlspecialchars($book['photo']) ?>" alt="Photo du livre" id="bookPhotoPreview">
                                    <?php else: ?>
                                        <!-- Image par défaut si le livre n'a pas de photo -->
                                        <img src="/tomtroc/public/images/book-example.png" alt="Photo du livre" id="bookPhotoPreview">
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Label cliquable pour déclencher le sélecteur de fichier -->
                                <label for="photo" class="photo-upload-label">
                                    <span>Modifier la photo</span>
                                </label>
                                
                                <!-- Input file caché (déclenché par le label) -->
                                <input 
                                    type="file" 
                                    id="photo" 
                                    name="photo" 
                                    accept="image/*"
                                    class="photo-input-hidden"
                                >
                            </div>
                        </div>

                        <!-- Colonne droite : Champs de modification -->
                        <div class="form-fields-section">
                            
                            <!-- Champ Titre : Obligatoire, pré-rempli avec la valeur actuelle -->
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

                            <!-- Champ Auteur : Obligatoire, pré-rempli avec la valeur actuelle -->
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

                            <!-- 
                                Champ Commentaire (Description) : Optionnel
                                Zone de texte multiligne pré-remplie avec la description actuelle
                                Utilise ?? '' pour éviter les erreurs si la description est null
                            -->
                            <div class="form-group-edit">
                                <label for="description" class="form-label-edit">Commentaire</label>
                                <textarea 
                                    id="description" 
                                    name="description" 
                                    class="form-textarea-edit"
                                ><?= htmlspecialchars($book['description'] ?? '') ?></textarea>
                            </div>

                            <!-- 
                                Champ Disponibilité : Menu déroulant personnalisé
                                Permet de changer le statut disponible/non disponible
                                L'option correspondant à l'état actuel est présélectionnée
                            -->
                            <div class="form-group-edit">
                                <label for="disponible" class="form-label-edit">Disponibilité</label>
                                <div class="select-wrapper">
                                    <select 
                                        id="disponible" 
                                        name="disponible" 
                                        class="form-select-edit"
                                    >
                                        <!-- Option "disponible" sélectionnée si le livre est actuellement disponible -->
                                        <option value="1" <?= $book['disponible'] ? 'selected' : '' ?>>disponible</option>
                                        
                                        <!-- Option "non disponible" sélectionnée si le livre n'est pas disponible -->
                                        <option value="0" <?= !$book['disponible'] ? 'selected' : '' ?>>non disponible</option>
                                    </select>
                                    
                                    <!-- Icône de flèche pour le select personnalisé -->
                                    <svg class="select-arrow" width="14" height="7" viewBox="0 0 14 7" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1L7 6L13 1" stroke="#000000" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Bouton de validation : Soumet le formulaire -->
                            <button type="submit" class="btn-validate">Valider</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>

        <!-- Inclusion du footer (pied de page) -->
        <?php require_once ROOT . DS . 'src' . DS . 'views' . DS . 'layout' . DS . 'footer.php'; ?>
    </div>

    <!--
        Script JavaScript : Preview instantané de la photo
        
        Fonctionnalité :
        - Affiche immédiatement la nouvelle photo sélectionnée
        - Permet de visualiser l'image avant de valider le formulaire
        - Améliore l'expérience utilisateur
    -->
    <script>
        /**
         * Gestionnaire d'événement pour le changement de photo
         * Déclenché lorsque l'utilisateur sélectionne un nouveau fichier
         */
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                /**
                 * Utilise FileReader pour lire le fichier image
                 * et l'afficher immédiatement dans la zone de preview
                 */
                const reader = new FileReader();
                reader.onload = function(event) {
                    // Remplace l'image de preview par la nouvelle photo sélectionnée
                    document.getElementById('bookPhotoPreview').src = event.target.result;
                };
                // Lit le fichier comme Data URL pour l'affichage
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>

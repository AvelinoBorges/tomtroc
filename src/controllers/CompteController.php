<?php

/**
 * Contrôleur du compte utilisateur
 * 
 * Gère toutes les fonctionnalités liées au compte de l'utilisateur :
 * - Affichage et modification du profil personnel
 * - Gestion de la bibliothèque de livres (ajout, édition, suppression)
 * - Téléchargement d'images (photos de profil et couvertures de livres)
 * - Authentification et vérification des permissions
 */
class CompteController extends Controller
{
    /**
     * Affiche la page du compte utilisateur
     * 
     * Récupère et affiche les informations personnelles de l'utilisateur connecté,
     * ainsi que la liste de ses livres. Calcule également la durée d'inscription
     * et formate toutes les données pour l'affichage.
     * Redirige vers la page de connexion si l'utilisateur n'est pas authentifié.
     * 
     * @return void
     */
    public function index(): void
    {
        // Vérifier si l'utilisateur est authentifié
        if (!isset($_SESSION['user'])) {
            $_SESSION['login_error'] = 'Vous devez être connecté pour accéder à cette page.';
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Récupérer l'identifiant de l'utilisateur depuis la session
        $userId = $_SESSION['user']['id'];

        // Rechercher les informations complètes de l'utilisateur dans la base
        $userModel = new User();
        $userData = $userModel->findById($userId);

        // Si l'utilisateur n'existe plus, détruire la session et rediriger
        if (!$userData) {
            session_destroy();
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Récupérer tous les livres appartenant à l'utilisateur
        $bookModel = new Book();
        $books = $bookModel->findByUserId($userId);
        $bookCount = count($books);

        // Calculer la durée d'inscription (temps écoulé depuis la création du compte)
        $dateCreation = new DateTime($userData['date_creation']);
        $now = new DateTime();
        $interval = $dateCreation->diff($now);
        
        // Formater le texte de la durée d'inscription de manière lisible
        if ($interval->y > 0) {
            $memberSince = $interval->y > 1 ? "Membre depuis {$interval->y} ans" : "Membre depuis 1 an";
        } elseif ($interval->m > 0) {
            $memberSince = $interval->m > 1 ? "Membre depuis {$interval->m} mois" : "Membre depuis 1 mois";
        } else {
            $memberSince = "Membre depuis moins d'un mois";
        }

        // Préparer les données formatées de l'utilisateur pour l'affichage
        $user = [
            'id' => $userData['id'],
            'pseudo' => $userData['pseudo'],
            'email' => $userData['email'],
            'nom' => $userData['nom'] ?? '',
            'prenom' => $userData['prenom'] ?? '',
            'password' => '•••••••••', // Masquer le mot de passe
            'memberSince' => $memberSince,
            'bookCount' => $bookCount > 1 ? "{$bookCount} livres" : ($bookCount == 1 ? "1 livre" : "0 livre"),
            'photoUrl' => !empty($userData['photo']) 
                ? '/tomtroc/public/images/' . $userData['photo'] 
                : '/tomtroc/public/images/default-avatar.svg'
        ];

        // Récupérer les messages de succès ou d'erreur depuis la session
        $success = $_SESSION['compte_success'] ?? null;
        $error = $_SESSION['compte_error'] ?? null;
        unset($_SESSION['compte_success']);
        unset($_SESSION['compte_error']);

        // Afficher la vue du compte avec toutes les données
        $this->render('compte/index', [
            'user' => $user,
            'books' => $books,
            'success' => $success,
            'error' => $error
        ]);
    }

    /**
     * Affiche le formulaire d'ajout d'un nouveau livre
     * 
     * Permet à l'utilisateur connecté d'accéder au formulaire pour ajouter
     * un nouveau livre à sa bibliothèque personnelle.
     * Redirige vers la page de connexion si l'utilisateur n'est pas authentifié.
     * 
     * @return void
     */
    public function add(): void
    {
        // Vérifier si l'utilisateur est authentifié
        if (!isset($_SESSION['user'])) {
            $_SESSION['login_error'] = 'Vous devez être connecté pour accéder à cette page.';
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Définir le titre de la page et récupérer les erreurs éventuelles
        $pageTitle = 'Ajouter un livre - Tom Troc';
        $error = $_SESSION['add_book_error'] ?? null;
        unset($_SESSION['add_book_error']);

        // Afficher le formulaire d'ajout
        $this->render('compte/add', [
            'pageTitle' => $pageTitle,
            'error' => $error
        ]);
    }

    /**
     * Traite la soumission du formulaire d'ajout de livre
     * 
     * Valide les données du formulaire, gère le téléchargement de l'image de couverture,
     * crée le nouveau livre dans la base de données et l'associe à l'utilisateur connecté.
     * Redirige vers le compte avec un message de succès ou d'erreur.
     * 
     * @return void
     */
    public function processAdd(): void
    {
        // Vérifier si l'utilisateur est authentifié
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // S'assurer que la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tomtroc/compte/add');
            exit;
        }

        // Récupérer et nettoyer les données du formulaire
        $titre = trim($_POST['titre'] ?? '');
        $auteur = trim($_POST['auteur'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $disponible = isset($_POST['disponible']) ? 1 : 0;

        // Valider que les champs obligatoires sont remplis
        if (empty($titre) || empty($auteur)) {
            $_SESSION['add_book_error'] = 'Le titre et l\'auteur sont obligatoires.';
            header('Location: /tomtroc/compte/add');
            exit;
        }

        try {
            // Instancier le modèle Book
            $bookModel = new Book();
            
            // Traiter le téléchargement de l'image de couverture si fournie
            $photoPath = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->uploadBookPhoto($_FILES['photo']);
            }

            // Créer le nouveau livre dans la base de données
            $bookId = $bookModel->create([
                'utilisateur_id' => $_SESSION['user']['id'],
                'titre' => $titre,
                'auteur' => $auteur,
                'description' => $description,
                'photo' => $photoPath,
                'disponible' => $disponible
            ]);

            if ($bookId) {
                // Succès - rediriger vers le compte avec un message
                $_SESSION['compte_success'] = 'Livre ajouté avec succès !';
                header('Location: /tomtroc/compte');
                exit;
            } else {
                // Échec de la création
                $_SESSION['add_book_error'] = 'Erreur lors de l\'ajout du livre.';
                header('Location: /tomtroc/compte/add');
                exit;
            }
        } catch (Exception $e) {
            // Gérer les erreurs de base de données
            error_log("Erreur lors de l'ajout du livre: " . $e->getMessage());
            $_SESSION['add_book_error'] = 'Une erreur est survenue.';
            header('Location: /tomtroc/compte/add');
            exit;
        }
    }

    /**
     * Traite la mise à jour du profil utilisateur
     * 
     * Valide et met à jour les informations personnelles de l'utilisateur :
     * email, pseudo, mot de passe et photo de profil. Vérifie l'unicité
     * de l'email et du pseudo avant la mise à jour.
     * Synchronise les données de session après modification.
     * 
     * @return void
     */
    public function updateProfile(): void
    {
        // Vérifier si l'utilisateur est authentifié
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // S'assurer que la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tomtroc/compte');
            exit;
        }

        // Récupérer et nettoyer les données du formulaire
        $email = trim($_POST['email'] ?? '');
        $pseudo = trim($_POST['pseudo'] ?? '');
        $password = $_POST['password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        // Valider que les champs obligatoires sont remplis
        if (empty($email) || empty($pseudo)) {
            $_SESSION['compte_error'] = 'L\'email et le pseudo sont obligatoires.';
            header('Location: /tomtroc/compte');
            exit;
        }

        // Valider le format de l'adresse email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['compte_error'] = 'Adresse email invalide.';
            header('Location: /tomtroc/compte');
            exit;
        }

        try {
            // Instancier le modèle utilisateur
            $userModel = new User();
            $userId = $_SESSION['user']['id'];

            // Vérifier si l'email est déjà utilisé par un autre utilisateur
            $existingUser = $userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $userId) {
                $_SESSION['compte_error'] = 'Cette adresse email est déjà utilisée.';
                header('Location: /tomtroc/compte');
                exit;
            }

            // Vérifier si le pseudo est déjà utilisé par un autre utilisateur
            $existingPseudo = $userModel->pseudoExists($pseudo);
            if ($existingPseudo) {
                $pseudoUser = $userModel->findById($userId);
                if ($pseudoUser['pseudo'] !== $pseudo) {
                    $_SESSION['compte_error'] = 'Ce pseudo est déjà utilisé.';
                    header('Location: /tomtroc/compte');
                    exit;
                }
            }

            // Préparer le tableau de données à mettre à jour
            $updateData = [
                'email' => $email,
                'pseudo' => $pseudo
            ];

            // Traiter le téléchargement d'une nouvelle photo de profil si fournie
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->uploadProfilePhoto($_FILES['photo']);
                if ($photoPath) {
                    $updateData['photo'] = $photoPath;
                }
            }

            // Si un nouveau mot de passe a été fourni, le valider et l'ajouter
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    $_SESSION['compte_error'] = 'Le mot de passe doit contenir au moins 6 caractères.';
                    header('Location: /tomtroc/compte');
                    exit;
                }
                $updateData['password'] = $newPassword;
            }

            // Effectuer la mise à jour dans la base de données
            if ($userModel->update($userId, $updateData)) {
                // Synchroniser les données de session avec les nouvelles valeurs
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['pseudo'] = $pseudo;

                $_SESSION['compte_success'] = 'Profil mis à jour avec succès !';
            } else {
                $_SESSION['compte_error'] = 'Aucune modification effectuée.';
            }

            header('Location: /tomtroc/compte');
            exit;

        } catch (Exception $e) {
            // Gérer les erreurs de base de données
            error_log("Erreur lors de la mise à jour du profil: " . $e->getMessage());
            $_SESSION['compte_error'] = 'Une erreur est survenue.';
            header('Location: /tomtroc/compte');
            exit;
        }
    }

    /**
     * Affiche le formulaire d'édition d'un livre
     * 
     * Permet à l'utilisateur de modifier un livre lui appartenant.
     * Vérifie que le livre existe et appartient bien à l'utilisateur connecté
     * avant d'afficher le formulaire d'édition.
     * 
     * @param int $id L'identifiant du livre à éditer
     * @return void
     */
    public function editBook(int $id): void
    {
        // Vérifier si l'utilisateur est authentifié
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Récupérer le livre depuis la base de données
        $bookModel = new Book();
        $book = $bookModel->findById($id);

        // Vérifier que le livre existe et appartient bien à l'utilisateur
        if (!$book || $book['utilisateur_id'] != $_SESSION['user']['id']) {
            $_SESSION['compte_error'] = 'Livre non trouvé.';
            header('Location: /tomtroc/compte');
            exit;
        }

        // Définir le titre de la page et récupérer les erreurs éventuelles
        $pageTitle = 'Éditer un livre - Tom Troc';
        $error = $_SESSION['edit_book_error'] ?? null;
        unset($_SESSION['edit_book_error']);

        // Afficher le formulaire d'édition avec les données du livre
        $this->render('compte/edit', [
            'pageTitle' => $pageTitle,
            'book' => $book,
            'error' => $error
        ]);
    }

    /**
     * Traite la soumission du formulaire d'édition de livre
     * 
     * Valide les données, vérifie que le livre appartient à l'utilisateur,
     * gère le téléchargement d'une nouvelle image si fournie, et met à jour
     * les informations du livre dans la base de données.
     * 
     * @param int $id L'identifiant du livre à mettre à jour
     * @return void
     */
    public function processEdit(int $id): void
    {
        // Vérifier si l'utilisateur est authentifié
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // S'assurer que la requête est de type POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tomtroc/compte');
            exit;
        }

        // Récupérer le livre depuis la base de données
        $bookModel = new Book();
        $book = $bookModel->findById($id);

        // Vérifier que le livre existe et appartient bien à l'utilisateur
        if (!$book || $book['utilisateur_id'] != $_SESSION['user']['id']) {
            $_SESSION['compte_error'] = 'Livre non trouvé.';
            header('Location: /tomtroc/compte');
            exit;
        }

        // Récupérer et nettoyer les données du formulaire
        $titre = trim($_POST['titre'] ?? '');
        $auteur = trim($_POST['auteur'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $disponible = isset($_POST['disponible']) ? (int)$_POST['disponible'] : 0;

        // Valider que les champs obligatoires sont remplis
        if (empty($titre) || empty($auteur)) {
            $_SESSION['edit_book_error'] = 'Le titre et l\'auteur sont obligatoires.';
            header('Location: /tomtroc/compte/editBook/' . $id);
            exit;
        }

        try {
            // Préparer le tableau de données à mettre à jour
            $updateData = [
                'titre' => $titre,
                'auteur' => $auteur,
                'description' => $description,
                'disponible' => $disponible
            ];

            // Traiter le téléchargement d'une nouvelle image de couverture si fournie
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->uploadBookPhoto($_FILES['photo']);
                if ($photoPath) {
                    $updateData['photo'] = $photoPath;
                }
            }

            // Effectuer la mise à jour dans la base de données
            if ($bookModel->update($id, $updateData)) {
                $_SESSION['compte_success'] = 'Livre mis à jour avec succès !';
            } else {
                $_SESSION['compte_error'] = 'Aucune modification effectuée.';
            }

            header('Location: /tomtroc/compte');
            exit;

        } catch (Exception $e) {
            // Gérer les erreurs de base de données
            error_log("Erreur lors de la modification du livre: " . $e->getMessage());
            $_SESSION['edit_book_error'] = 'Une erreur est survenue.';
            header('Location: /tomtroc/compte/editBook/' . $id);
            exit;
        }
    }

    /**
     * Supprime un livre de la bibliothèque de l'utilisateur
     * 
     * Vérifie que le livre appartient bien à l'utilisateur connecté avant
     * de procéder à la suppression. Redirige vers le compte avec un message
     * de confirmation ou d'erreur.
     * 
     * @param int $id L'identifiant du livre à supprimer
     * @return void
     */
    public function deleteBook(int $id): void
    {
        // Vérifier si l'utilisateur est authentifié
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        try {
            // Instancier le modèle et tenter la suppression
            $bookModel = new Book();
            
            // La méthode delete vérifie automatiquement que le livre appartient à l'utilisateur
            if ($bookModel->delete($id, $_SESSION['user']['id'])) {
                $_SESSION['compte_success'] = 'Livre supprimé avec succès !';
            } else {
                $_SESSION['compte_error'] = 'Impossible de supprimer ce livre.';
            }
        } catch (Exception $e) {
            // Gérer les erreurs de base de données
            error_log("Erreur lors de la suppression du livre: " . $e->getMessage());
            $_SESSION['compte_error'] = 'Une erreur est survenue.';
        }

        // Rediriger vers la page du compte
        header('Location: /tomtroc/compte');
        exit;
    }

    /**
     * Gère le téléchargement de l'image de couverture d'un livre
     * 
     * Valide le type de fichier (JPEG, PNG, GIF), la taille (max 5MB),
     * génère un nom unique pour éviter les collisions, et déplace le fichier
     * dans le répertoire approprié. Crée le répertoire s'il n'existe pas.
     * 
     * @param array $file Le tableau $_FILES contenant les informations du fichier
     * @return string|null Le chemin relatif du fichier téléchargé, ou null en cas d'erreur
     */
    private function uploadBookPhoto(array $file): ?string
    {
        // Types de fichiers autorisés pour les images
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // Taille maximale : 5MB

        // Vérifier que le type de fichier est autorisé
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        // Vérifier que la taille du fichier est acceptable
        if ($file['size'] > $maxSize) {
            return null;
        }

        // Définir le répertoire de destination
        $uploadDir = ROOT . DS . 'public' . DS . 'images' . DS . 'books' . DS;
        
        // Créer le répertoire s'il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Générer un nom de fichier unique pour éviter les collisions
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('book_') . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Déplacer le fichier téléchargé vers le répertoire de destination
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'books/' . $filename;
        }

        return null;
    }

    /**
     * Gère le téléchargement de la photo de profil d'un utilisateur
     * 
     * Valide le type de fichier (JPEG, PNG, GIF), la taille (max 5MB),
     * génère un nom unique pour éviter les collisions, et déplace le fichier
     * dans le répertoire approprié. Crée le répertoire s'il n'existe pas.
     * 
     * @param array $file Le tableau $_FILES contenant les informations du fichier
     * @return string|null Le chemin relatif du fichier téléchargé, ou null en cas d'erreur
     */
    private function uploadProfilePhoto(array $file): ?string
    {
        // Types de fichiers autorisés pour les images
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // Taille maximale : 5MB

        // Vérifier que le type de fichier est autorisé
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        // Vérifier que la taille du fichier est acceptable
        if ($file['size'] > $maxSize) {
            return null;
        }

        // Définir le répertoire de destination pour les photos de profil
        $uploadDir = ROOT . DS . 'public' . DS . 'images' . DS . 'profiles' . DS;
        
        // Créer le répertoire s'il n'existe pas
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Générer un nom de fichier unique pour éviter les collisions
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_') . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Déplacer le fichier téléchargé vers le répertoire de destination
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'profiles/' . $filename;
        }

        return null;
    }
}

<?php

class CompteController extends Controller
{
    public function index(): void
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            $_SESSION['login_error'] = 'Vous devez être connecté pour accéder à cette page.';
            header('Location: /tomtroc/auth/login');
            exit;
        }

        $userId = $_SESSION['user']['id'];

        // Rechercher les informations de l'utilisateur
        $userModel = new User();
        $userData = $userModel->findById($userId);

        if (!$userData) {
            session_destroy();
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Rechercher les livres de l'utilisateur
        $bookModel = new Book();
        $books = $bookModel->findByUserId($userId);
        $bookCount = count($books);

        // Calculer le temps de membre
        $dateCreation = new DateTime($userData['date_creation']);
        $now = new DateTime();
        $interval = $dateCreation->diff($now);
        
        if ($interval->y > 0) {
            $memberSince = $interval->y > 1 ? "Membre depuis {$interval->y} ans" : "Membre depuis 1 an";
        } elseif ($interval->m > 0) {
            $memberSince = $interval->m > 1 ? "Membre depuis {$interval->m} mois" : "Membre depuis 1 mois";
        } else {
            $memberSince = "Membre depuis moins d'un mois";
        }

        // Préparer les données de l'utilisateur pour la vue
        $user = [
            'id' => $userData['id'],
            'pseudo' => $userData['pseudo'],
            'email' => $userData['email'],
            'nom' => $userData['nom'] ?? '',
            'prenom' => $userData['prenom'] ?? '',
            'password' => '•••••••••',
            'memberSince' => $memberSince,
            'bookCount' => $bookCount > 1 ? "{$bookCount} livres" : ($bookCount == 1 ? "1 livre" : "0 livre"),
            'photoUrl' => !empty($userData['photo']) 
                ? '/tomtroc/public/images/' . $userData['photo'] 
                : '/tomtroc/public/images/default-avatar.svg'
        ];

        // Messages de succès/erreur
        $success = $_SESSION['compte_success'] ?? null;
        $error = $_SESSION['compte_error'] ?? null;
        unset($_SESSION['compte_success']);
        unset($_SESSION['compte_error']);

        $this->render('compte/index', [
            'user' => $user,
            'books' => $books,
            'success' => $success,
            'error' => $error
        ]);
    }

    public function add(): void
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            $_SESSION['login_error'] = 'Vous devez être connecté pour accéder à cette page.';
            header('Location: /tomtroc/auth/login');
            exit;
        }

        $pageTitle = 'Ajouter un livre - Tom Troc';
        $error = $_SESSION['add_book_error'] ?? null;
        unset($_SESSION['add_book_error']);

        $this->render('compte/add', [
            'pageTitle' => $pageTitle,
            'error' => $error
        ]);
    }

    public function processAdd(): void
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Vérifier si c'est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tomtroc/compte/add');
            exit;
        }

        $titre = trim($_POST['titre'] ?? '');
        $auteur = trim($_POST['auteur'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $disponible = isset($_POST['disponible']) ? 1 : 0;

        // Validation
        if (empty($titre) || empty($auteur)) {
            $_SESSION['add_book_error'] = 'Le titre et l\'auteur sont obligatoires.';
            header('Location: /tomtroc/compte/add');
            exit;
        }

        try {
            $bookModel = new Book();
            
            // Traiter le téléchargement de l'image (si présente)
            $photoPath = null;
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->uploadBookPhoto($_FILES['photo']);
            }

            $bookId = $bookModel->create([
                'utilisateur_id' => $_SESSION['user']['id'],
                'titre' => $titre,
                'auteur' => $auteur,
                'description' => $description,
                'photo' => $photoPath,
                'disponible' => $disponible
            ]);

            if ($bookId) {
                $_SESSION['compte_success'] = 'Livre ajouté avec succès !';
                header('Location: /tomtroc/compte');
                exit;
            } else {
                $_SESSION['add_book_error'] = 'Erreur lors de l\'ajout du livre.';
                header('Location: /tomtroc/compte/add');
                exit;
            }
        } catch (Exception $e) {
            error_log("Erreur lors de l'ajout du livre: " . $e->getMessage());
            $_SESSION['add_book_error'] = 'Une erreur est survenue.';
            header('Location: /tomtroc/compte/add');
            exit;
        }
    }

    public function updateProfile(): void
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Vérifier si c'est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tomtroc/compte');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $pseudo = trim($_POST['pseudo'] ?? '');
        $password = $_POST['password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        // Validation
        if (empty($email) || empty($pseudo)) {
            $_SESSION['compte_error'] = 'L\'email et le pseudo sont obligatoires.';
            header('Location: /tomtroc/compte');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['compte_error'] = 'Adresse email invalide.';
            header('Location: /tomtroc/compte');
            exit;
        }

        try {
            $userModel = new User();
            $userId = $_SESSION['user']['id'];

            // Vérifier si l'email ou le pseudo existe déjà (pour un autre utilisateur)
            $existingUser = $userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] != $userId) {
                $_SESSION['compte_error'] = 'Cette adresse email est déjà utilisée.';
                header('Location: /tomtroc/compte');
                exit;
            }

            $existingPseudo = $userModel->pseudoExists($pseudo);
            if ($existingPseudo) {
                $pseudoUser = $userModel->findById($userId);
                if ($pseudoUser['pseudo'] !== $pseudo) {
                    $_SESSION['compte_error'] = 'Ce pseudo est déjà utilisé.';
                    header('Location: /tomtroc/compte');
                    exit;
                }
            }

            // Préparer les données pour la mise à jour
            $updateData = [
                'email' => $email,
                'pseudo' => $pseudo
            ];

            // Traiter le téléchargement de la photo de profil
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->uploadProfilePhoto($_FILES['photo']);
                if ($photoPath) {
                    $updateData['photo'] = $photoPath;
                }
            }

            // Si un nouveau mot de passe a été fourni
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    $_SESSION['compte_error'] = 'Le mot de passe doit contenir au moins 6 caractères.';
                    header('Location: /tomtroc/compte');
                    exit;
                }
                $updateData['password'] = $newPassword;
            }

            // Mettre à jour
            if ($userModel->update($userId, $updateData)) {
                // Mettre à jour la session
                $_SESSION['user']['email'] = $email;
                $_SESSION['user']['pseudo'] = $pseudo;

                $_SESSION['compte_success'] = 'Profil mis à jour avec succès !';
            } else {
                $_SESSION['compte_error'] = 'Aucune modification effectuée.';
            }

            header('Location: /tomtroc/compte');
            exit;

        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour du profil: " . $e->getMessage());
            $_SESSION['compte_error'] = 'Une erreur est survenue.';
            header('Location: /tomtroc/compte');
            exit;
        }
    }

    public function editBook(int $id): void
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        $bookModel = new Book();
        $book = $bookModel->findById($id);

        // Vérifier si le livre existe et appartient à l'utilisateur
        if (!$book || $book['utilisateur_id'] != $_SESSION['user']['id']) {
            $_SESSION['compte_error'] = 'Livre non trouvé.';
            header('Location: /tomtroc/compte');
            exit;
        }

        $pageTitle = 'Éditer un livre - Tom Troc';
        $error = $_SESSION['edit_book_error'] ?? null;
        unset($_SESSION['edit_book_error']);

        $this->render('compte/edit', [
            'pageTitle' => $pageTitle,
            'book' => $book,
            'error' => $error
        ]);
    }

    public function processEdit(int $id): void
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Vérifier si c'est POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tomtroc/compte');
            exit;
        }

        $bookModel = new Book();
        $book = $bookModel->findById($id);

        // Vérifier si le livre existe et appartient à l'utilisateur
        if (!$book || $book['utilisateur_id'] != $_SESSION['user']['id']) {
            $_SESSION['compte_error'] = 'Livre non trouvé.';
            header('Location: /tomtroc/compte');
            exit;
        }

        $titre = trim($_POST['titre'] ?? '');
        $auteur = trim($_POST['auteur'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $disponible = isset($_POST['disponible']) ? (int)$_POST['disponible'] : 0;

        // Validation
        if (empty($titre) || empty($auteur)) {
            $_SESSION['edit_book_error'] = 'Le titre et l\'auteur sont obligatoires.';
            header('Location: /tomtroc/compte/editBook/' . $id);
            exit;
        }

        try {
            $updateData = [
                'titre' => $titre,
                'auteur' => $auteur,
                'description' => $description,
                'disponible' => $disponible
            ];

            // Traiter le téléchargement d'une nouvelle image (si présente)
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photoPath = $this->uploadBookPhoto($_FILES['photo']);
                if ($photoPath) {
                    $updateData['photo'] = $photoPath;
                }
            }

            if ($bookModel->update($id, $updateData)) {
                $_SESSION['compte_success'] = 'Livre mis à jour avec succès !';
            } else {
                $_SESSION['compte_error'] = 'Aucune modification effectuée.';
            }

            header('Location: /tomtroc/compte');
            exit;

        } catch (Exception $e) {
            error_log("Erreur lors de la modification du livre: " . $e->getMessage());
            $_SESSION['edit_book_error'] = 'Une erreur est survenue.';
            header('Location: /tomtroc/compte/editBook/' . $id);
            exit;
        }
    }

    public function deleteBook(int $id): void
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user'])) {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        try {
            $bookModel = new Book();
            
            if ($bookModel->delete($id, $_SESSION['user']['id'])) {
                $_SESSION['compte_success'] = 'Livre supprimé avec succès !';
            } else {
                $_SESSION['compte_error'] = 'Impossible de supprimer ce livre.';
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la suppression du livre: " . $e->getMessage());
            $_SESSION['compte_error'] = 'Une erreur est survenue.';
        }

        header('Location: /tomtroc/compte');
        exit;
    }

    /**
     * Téléchargement de la photo du livre
     * 
     * @param array $file
     * @return string|null
     */
    private function uploadBookPhoto(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        if ($file['size'] > $maxSize) {
            return null;
        }

        $uploadDir = ROOT . DS . 'public' . DS . 'images' . DS . 'books' . DS;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('book_') . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'books/' . $filename;
        }

        return null;
    }

    /**
     * Téléchargement de la photo de profil de l'utilisateur
     * 
     * @param array $file
     * @return string|null
     */
    private function uploadProfilePhoto(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        if ($file['size'] > $maxSize) {
            return null;
        }

        $uploadDir = ROOT . DS . 'public' . DS . 'images' . DS . 'profiles' . DS;
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_') . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'profiles/' . $filename;
        }

        return null;
    }
}

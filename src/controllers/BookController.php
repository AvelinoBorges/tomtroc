<?php

class BookController extends Controller
{
    private $bookModel;

    public function __construct()
    {
        $this->bookModel = new Book();
    }

    public function index()
    {
        // Redirection vers la liste des livres si pas d'ID
        header('Location: /tomtroc/books');
        exit;
    }

    public function show($id = null)
    {
        if (!$id) {
            header('Location: /tomtroc/books');
            exit;
        }

        // Rechercher le livre dans la base de données avec les informations du propriétaire
        $bookData = $this->bookModel->findByIdWithOwner((int)$id);

        if (!$bookData) {
            // Livre non trouvé, rediriger vers la liste
            $_SESSION['error'] = "Livre non trouvé.";
            header('Location: /tomtroc/books');
            exit;
        }

        // Formater les données pour la vue
        $book = [
            'id' => $bookData['id'],
            'title' => $bookData['titre'],
            'author' => $bookData['auteur'],
            'description' => $bookData['description'] ?? 'Pas de description disponible.',
            'available' => $bookData['disponible'],
            'created_at' => $bookData['date_creation'],
            'image' => $this->formatImagePath($bookData['photo']),
            'owner' => [
                'id' => $bookData['owner_id'],
                'username' => $bookData['owner_username'],
                'email' => $bookData['owner_email'],
                'avatar' => $this->formatAvatarPath($bookData['owner_photo']),
                'member_since' => $bookData['owner_since']
            ]
        ];

        // Vérifier si l'utilisateur est connecté
        $isLoggedIn = isset($_SESSION['user']);
        $isOwner = false;

        if ($isLoggedIn) {
            $isOwner = $_SESSION['user']['id'] == $book['owner']['id'];
        }

        $pageTitle = htmlspecialchars($book['title']) . ' - TomTroc';

        require_once ROOT . DS . 'src' . DS . 'views' . DS . 'book' . DS . 'show.php';
    }

    /**
     * Formate le chemin de l'image du livre
     */
    private function formatImagePath(?string $photo): string
    {
        if (empty($photo)) {
            return '/tomtroc/public/images/default-image.png';
        }

        // Si commence déjà par books/, ajoute seulement le chemin de base
        if (strpos($photo, 'books/') === 0) {
            return '/tomtroc/public/images/' . $photo;
        }

        // Sinon, ajoute le chemin complet
        return '/tomtroc/public/images/books/' . $photo;
    }

    /**
     * Formate le chemin de l'avatar de l'utilisateur
     */
    private function formatAvatarPath(?string $photo): string
    {
        if (empty($photo)) {
            return '/tomtroc/public/images/default-avatar.svg';
        }

        // Si commence déjà par profiles/, ajoute seulement le chemin de base
        if (strpos($photo, 'profiles/') === 0) {
            return '/tomtroc/public/images/' . $photo;
        }

        // Sinon, ajoute le chemin complet
        return '/tomtroc/public/images/profiles/' . $photo;
    }
}

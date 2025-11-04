<?php

/**
 * Contrôleur de livre individuel
 * 
 * Gère l'affichage détaillé d'un livre spécifique avec les informations
 * du propriétaire. Inclut la gestion des chemins d'images pour les couvertures
 * de livres et les avatars des utilisateurs.
 */
class BookController extends Controller
{
    /**
     * Instance du modèle Book pour les opérations en base de données
     * @var Book
     */
    private $bookModel;

    /**
     * Constructeur du contrôleur
     * 
     * Initialise le modèle Book pour accéder aux données des livres.
     */
    public function __construct()
    {
        $this->bookModel = new Book();
    }

    /**
     * Page d'index par défaut
     * 
     * Redirige automatiquement vers la liste complète des livres
     * car cette page nécessite un ID de livre spécifique.
     * 
     * @return void
     */
    public function index()
    {
        // Redirection vers la liste des livres si aucun ID n'est fourni
        header('Location: /tomtroc/books');
        exit;
    }

    /**
     * Affiche la page détaillée d'un livre spécifique
     * 
     * Récupère toutes les informations du livre ainsi que les données de son propriétaire,
     * formate les données pour l'affichage, et détermine si l'utilisateur connecté
     * est le propriétaire du livre. Redirige vers la liste si le livre n'existe pas.
     * 
     * @param int|null $id L'identifiant du livre à afficher
     * @return void
     */
    public function show($id = null)
    {
        // Vérifier qu'un ID est fourni, sinon rediriger vers la liste
        if (!$id) {
            header('Location: /tomtroc/books');
            exit;
        }

        // Rechercher le livre dans la base de données avec les informations du propriétaire
        $bookData = $this->bookModel->findByIdWithOwner((int)$id);

        if (!$bookData) {
            // Livre non trouvé, rediriger vers la liste avec un message d'erreur
            $_SESSION['error'] = "Livre non trouvé.";
            header('Location: /tomtroc/books');
            exit;
        }

        // Formater les données pour la vue avec des noms de clés cohérents
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

        // Vérifier si un utilisateur est connecté
        $isLoggedIn = isset($_SESSION['user']);
        $isOwner = false;

        // Déterminer si l'utilisateur connecté est le propriétaire du livre
        if ($isLoggedIn) {
            $isOwner = $_SESSION['user']['id'] == $book['owner']['id'];
        }

        // Définir le titre de la page avec protection XSS
        $pageTitle = htmlspecialchars($book['title']) . ' - TomTroc';

        // Afficher la vue détaillée du livre
        require_once ROOT . DS . 'src' . DS . 'views' . DS . 'book' . DS . 'show.php';
    }

    /**
     * Formate le chemin de l'image de couverture du livre
     * 
     * Génère le chemin complet vers l'image de la couverture du livre.
     * Si aucune image n'est définie, retourne le chemin de l'image par défaut.
     * Gère les chemins qui incluent déjà le préfixe 'books/'.
     * 
     * @param string|null $photo Le nom du fichier de l'image ou null
     * @return string Le chemin complet vers l'image
     */
    private function formatImagePath(?string $photo): string
    {
        // Retourner l'image par défaut si aucune photo n'est fournie
        if (empty($photo)) {
            return '/tomtroc/public/images/default-image.png';
        }

        // Si le chemin commence déjà par 'books/', ajouter seulement le chemin de base
        if (strpos($photo, 'books/') === 0) {
            return '/tomtroc/public/images/' . $photo;
        }

        // Sinon, construire le chemin complet avec le dossier books/
        return '/tomtroc/public/images/books/' . $photo;
    }

    /**
     * Formate le chemin de l'avatar de l'utilisateur propriétaire
     * 
     * Génère le chemin complet vers l'avatar du propriétaire du livre.
     * Si aucune photo n'est définie, retourne le chemin de l'avatar par défaut.
     * Gère les chemins qui incluent déjà le préfixe 'profiles/'.
     * 
     * @param string|null $photo Le nom du fichier de l'avatar ou null
     * @return string Le chemin complet vers l'avatar
     */
    private function formatAvatarPath(?string $photo): string
    {
        // Retourner l'avatar par défaut si aucune photo n'est fournie
        if (empty($photo)) {
            return '/tomtroc/public/images/default-avatar.svg';
        }

        // Si le chemin commence déjà par 'profiles/', ajouter seulement le chemin de base
        if (strpos($photo, 'profiles/') === 0) {
            return '/tomtroc/public/images/' . $photo;
        }

        // Sinon, construire le chemin complet avec le dossier profiles/
        return '/tomtroc/public/images/profiles/' . $photo;
    }
}

<?php

class UserController extends Controller
{
    private User $userModel;
    private Book $bookModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->bookModel = new Book();
    }

    /**
     * Affiche le profil public d'un utilisateur
     * 
     * @param int $id ID de l'utilisateur
     */
    public function profile($id = null)
    {
        if (!$id) {
            $this->redirect('/tomtroc');
            return;
        }

        // Récupérer l'utilisateur
        $user = $this->userModel->findById($id);
        
        if (!$user) {
            http_response_code(404);
            echo "Utilisateur non trouvé";
            return;
        }

        // Récupérer tous les livres de l'utilisateur
        $books = $this->bookModel->findByUserId($id);

        // Formatar o caminho das fotos dos livros
        foreach ($books as &$book) {
            if (!empty($book['photo'])) {
                // Se a foto já não começa com /tomtroc, adicionar o caminho completo
                if (strpos($book['photo'], '/tomtroc') !== 0) {
                    $book['photo'] = '/tomtroc/public/images/' . $book['photo'];
                }
            }
        }
        unset($book); // Quebrar a referência

        // Calculer depuis combien de temps l'utilisateur est membre
        $createdDate = new DateTime($user['date_creation']);
        $now = new DateTime();
        $interval = $createdDate->diff($now);
        
        if ($interval->y > 0) {
            $memberSince = $interval->y . ' an' . ($interval->y > 1 ? 's' : '');
        } elseif ($interval->m > 0) {
            $memberSince = $interval->m . ' mois';
        } else {
            $memberSince = '1 mois';
        }

        // Vérifier si l'utilisateur est connecté
        $isLoggedIn = isset($_SESSION['user']);
        $currentUserId = $isLoggedIn ? $_SESSION['user']['id'] : null;
        $isOwnProfile = $isLoggedIn && $currentUserId == $id;

        // Photo de profil par défaut si non définie
        $profilePhoto = '/tomtroc/public/images/default-avatar.svg';
        if (!empty($user['photo'])) {
            // Se a foto já não começa com /tomtroc, adicionar o caminho completo
            if (strpos($user['photo'], '/tomtroc') !== 0) {
                $profilePhoto = '/tomtroc/public/images/' . $user['photo'];
            } else {
                $profilePhoto = $user['photo'];
            }
        }

        // Données pour la vue
        $data = [
            'pageTitle' => htmlspecialchars($user['pseudo']) . ' - Profil public',
            'user' => $user,
            'books' => $books,
            'bookCount' => count($books),
            'memberSince' => $memberSince,
            'profilePhoto' => $profilePhoto,
            'isLoggedIn' => $isLoggedIn,
            'isOwnProfile' => $isOwnProfile
        ];

        $this->render('user/profile', $data);
    }
}

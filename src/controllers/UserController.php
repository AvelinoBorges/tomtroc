<?php
/**
 * Contrôleur de gestion des profils utilisateur
 * 
 * Ce contrôleur gère l'affichage des profils publics des utilisateurs
 * de la plateforme TomTroc, incluant leurs informations personnelles
 * et leur bibliothèque de livres disponibles à l'échange.
 */

class UserController extends Controller
{
    /**
     * @var User Instance du modèle User pour gérer les données utilisateur
     */
    private User $userModel;
    
    /**
     * @var Book Instance du modèle Book pour gérer les livres de l'utilisateur
     */
    private Book $bookModel;

    /**
     * Constructeur du contrôleur
     * 
     * Initialise les modèles nécessaires pour accéder aux données
     * des utilisateurs et de leurs livres
     */
    public function __construct()
    {
        $this->userModel = new User();
        $this->bookModel = new Book();
    }

    /**
     * Affiche le profil public d'un utilisateur
     * 
     * Cette méthode récupère et affiche toutes les informations publiques
     * d'un utilisateur : pseudo, date d'inscription, photo de profil et
     * l'ensemble de sa bibliothèque de livres. Elle gère également la
     * détection du profil personnel (pour afficher des options supplémentaires).
     * 
     * @param int|null $id L'identifiant de l'utilisateur à afficher
     * @return void
     */
    public function profile($id = null)
    {
        // Vérifier qu'un ID valide a été fourni
        // Sans ID, redirection vers la page d'accueil
        if (!$id) {
            $this->redirect('/tomtroc');
            return;
        }

        // Récupérer les données de l'utilisateur depuis la base de données
        $user = $this->userModel->findById($id);
        
        // Si l'utilisateur n'existe pas, afficher une erreur 404
        if (!$user) {
            http_response_code(404);
            echo "Utilisateur non trouvé";
            return;
        }

        // Récupérer tous les livres appartenant à cet utilisateur
        // Permet d'afficher sa bibliothèque complète sur son profil
        $books = $this->bookModel->findByUserId($id);

        // Formater le chemin des photos de chaque livre pour l'affichage
        // Parcourir les livres par référence pour modifier directement le tableau
        foreach ($books as &$book) {
            if (!empty($book['photo'])) {
                // Si le chemin ne commence pas déjà par '/tomtroc', ajouter le chemin complet
                // Cela garantit que toutes les images ont un chemin absolu valide
                if (strpos($book['photo'], '/tomtroc') !== 0) {
                    $book['photo'] = '/tomtroc/public/images/' . $book['photo'];
                }
            }
        }
        unset($book); // Détruire la référence pour éviter des effets secondaires

        // Calculer l'ancienneté de l'utilisateur sur la plateforme
        // Cette information est affichée sur le profil (ex: "Membre depuis 2 ans")
        $createdDate = new DateTime($user['date_creation']);  // Date d'inscription de l'utilisateur
        $now = new DateTime();                                 // Date actuelle
        $interval = $createdDate->diff($now);                 // Calculer la différence entre les deux dates
        
        // Formater l'ancienneté de manière lisible
        if ($interval->y > 0) {
            // Si plus d'un an : afficher en années (avec pluriel si nécessaire)
            $memberSince = $interval->y . ' an' . ($interval->y > 1 ? 's' : '');
        } elseif ($interval->m > 0) {
            // Si moins d'un an mais plus d'un mois : afficher en mois
            $memberSince = $interval->m . ' mois';
        } else {
            // Si moins d'un mois : afficher "1 mois" par défaut
            $memberSince = '1 mois';
        }

        // Déterminer le statut de connexion et si c'est le profil de l'utilisateur actuel
        $isLoggedIn = isset($_SESSION['user']);                          // L'utilisateur est-il connecté ?
        $currentUserId = $isLoggedIn ? $_SESSION['user']['id'] : null;  // ID de l'utilisateur connecté (si applicable)
        $isOwnProfile = $isLoggedIn && $currentUserId == $id;           // Est-ce son propre profil ?

        // Définir la photo de profil avec une image par défaut si nécessaire
        $profilePhoto = '/tomtroc/public/images/default-avatar.svg';
        if (!empty($user['photo'])) {
            // Si une photo existe, vérifier et formater son chemin
            // Si le chemin ne commence pas par '/tomtroc', ajouter le préfixe complet
            if (strpos($user['photo'], '/tomtroc') !== 0) {
                $profilePhoto = '/tomtroc/public/images/' . $user['photo'];
            } else {
                $profilePhoto = $user['photo'];
            }
        }

        // Préparer toutes les données nécessaires pour la vue du profil
        $data = [
            'pageTitle' => htmlspecialchars($user['pseudo']) . ' - Profil public',  // Titre de la page (sécurisé contre XSS)
            'user' => $user,                                                         // Données complètes de l'utilisateur
            'books' => $books,                                                       // Liste des livres de l'utilisateur
            'bookCount' => count($books),                                           // Nombre total de livres
            'memberSince' => $memberSince,                                          // Ancienneté formatée (ex: "2 ans")
            'profilePhoto' => $profilePhoto,                                        // Chemin de la photo de profil
            'isLoggedIn' => $isLoggedIn,                                           // Statut de connexion du visiteur
            'isOwnProfile' => $isOwnProfile                                        // Indique si c'est son propre profil
        ];

        // Rendre la vue du profil avec toutes les données préparées
        $this->render('user/profile', $data);
    }
}

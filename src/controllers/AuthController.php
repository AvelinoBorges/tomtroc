<?php

/**
 * Contrôleur d'authentification
 * 
 * Gère toutes les opérations liées à l'authentification des utilisateurs :
 * - Connexion (login)
 * - Inscription (register)
 * - Déconnexion (logout)
 * - Traitement des formulaires d'authentification
 */
class AuthController extends Controller
{
    /**
     * Affiche la page de connexion
     * 
     * Si l'utilisateur est déjà connecté, il est redirigé vers la page d'accueil.
     * Récupère et affiche les messages d'erreur ou de succès stockés en session.
     * 
     * @return void
     */
    public function login()
    {
        // Si l'utilisateur est déjà connecté, rediriger vers la page d'accueil
        if (isset($_SESSION['user'])) {
            header('Location: /tomtroc/');
            exit;
        }

        // Définir le titre de la page et récupérer les messages de session
        $pageTitle = 'Connexion - Tom Troc';
        $error = $_SESSION['login_error'] ?? null;
        $success = $_SESSION['login_success'] ?? null;
        unset($_SESSION['login_error']);
        unset($_SESSION['login_success']);

        // Afficher la vue de connexion
        $this->render('auth/login', [
            'pageTitle' => $pageTitle,
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Affiche la page d'inscription
     * 
     * Si l'utilisateur est déjà connecté, il est redirigé vers la page d'accueil.
     * Récupère et affiche les messages d'erreur ou de succès stockés en session.
     * 
     * @return void
     */
    public function register()
    {
        // Si l'utilisateur est déjà connecté, rediriger vers la page d'accueil
        if (isset($_SESSION['user'])) {
            header('Location: /tomtroc/');
            exit;
        }

        // Définir le titre de la page et récupérer les messages de session
        $pageTitle = 'Inscription - Tom Troc';
        $error = $_SESSION['register_error'] ?? null;
        $success = $_SESSION['register_success'] ?? null;
        unset($_SESSION['register_error']);
        unset($_SESSION['register_success']);

        // Afficher la vue d'inscription
        $this->render('auth/register', [
            'pageTitle' => $pageTitle,
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Traite la soumission du formulaire de connexion
     * 
     * Valide les données du formulaire (email et mot de passe), vérifie les identifiants
     * dans la base de données, et crée une session utilisateur en cas de succès.
     * Redirige vers la page appropriée avec un message d'erreur ou de succès.
     * 
     * @return void
     */
    public function processLogin()
    {
        // Vérifier si c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Récupérer les données du formulaire
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation de base
        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = 'Veuillez remplir tous les champs.';
            header('Location: /tomtroc/auth/login');
            exit;
        }

        // Valider le format de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['login_error'] = 'Adresse email invalide.';
            header('Location: /tomtroc/auth/login');
            exit;
        }

        try {
            // Instancier le modèle utilisateur et vérifier les identifiants
            $userModel = new User();
            $user = $userModel->verifyCredentials($email, $password);

            if ($user) {
                // Connexion réussie - créer la session utilisateur avec ses informations
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'pseudo' => $user['pseudo'],
                    'email' => $user['email'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom']
                ];

                // Rediriger vers la page d'accueil
                header('Location: /tomtroc/');
                exit;
            } else {
                // Identifiants invalides - afficher un message d'erreur
                $_SESSION['login_error'] = 'Email ou mot de passe incorrect.';
                header('Location: /tomtroc/auth/login');
                exit;
            }
        } catch (Exception $e) {
            // Erreur lors de l'accès à la base de données
            error_log("Erreur lors de la connexion: " . $e->getMessage());
            $_SESSION['login_error'] = 'Une erreur est survenue. Veuillez réessayer.';
            header('Location: /tomtroc/auth/login');
            exit;
        }
    }

    /**
     * Traite la soumission du formulaire d'inscription
     * 
     * Valide les données du formulaire (pseudo, email, mot de passe, confirmation),
     * vérifie l'unicité de l'email et du pseudo, crée le nouvel utilisateur dans la
     * base de données et redirige vers la page de connexion en cas de succès.
     * 
     * @return void
     */
    public function processRegister()
    {
        // Vérifier si c'est une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tomtroc/auth/register');
            exit;
        }

        // Récupérer les données du formulaire
        $pseudo = trim($_POST['pseudo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation de base
        if (empty($pseudo) || empty($email) || empty($password) || empty($confirmPassword)) {
            $_SESSION['register_error'] = 'Veuillez remplir tous les champs.';
            header('Location: /tomtroc/auth/register');
            exit;
        }

        // Valider le format de l'email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['register_error'] = 'Adresse email invalide.';
            header('Location: /tomtroc/auth/register');
            exit;
        }

        // Vérifier si les mots de passe correspondent
        if ($password !== $confirmPassword) {
            $_SESSION['register_error'] = 'Les mots de passe ne correspondent pas.';
            header('Location: /tomtroc/auth/register');
            exit;
        }

        // Valider la longueur du mot de passe
        if (strlen($password) < 6) {
            $_SESSION['register_error'] = 'Le mot de passe doit contenir au moins 6 caractères.';
            header('Location: /tomtroc/auth/register');
            exit;
        }

        try {
            // Instancier le modèle utilisateur
            $userModel = new User();

            // Vérifier si l'adresse email est déjà utilisée
            if ($userModel->emailExists($email)) {
                $_SESSION['register_error'] = 'Cette adresse email est déjà utilisée.';
                header('Location: /tomtroc/auth/register');
                exit;
            }

            // Vérifier si le pseudo est déjà pris
            if ($userModel->pseudoExists($pseudo)) {
                $_SESSION['register_error'] = 'Ce pseudo est déjà utilisé.';
                header('Location: /tomtroc/auth/register');
                exit;
            }

            // Créer le nouvel utilisateur dans la base de données
            $userId = $userModel->create([
                'pseudo' => $pseudo,
                'email' => $email,
                'password' => $password
            ]);

            if ($userId) {
                // Inscription réussie - rediriger vers la page de connexion
                $_SESSION['login_success'] = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
                header('Location: /tomtroc/auth/login');
                exit;
            } else {
                // Échec de la création de l'utilisateur
                $_SESSION['register_error'] = 'Une erreur est survenue lors de l\'inscription.';
                header('Location: /tomtroc/auth/register');
                exit;
            }
        } catch (Exception $e) {
            // Erreur lors de l'accès à la base de données
            error_log("Erreur lors de l'inscription: " . $e->getMessage());
            $_SESSION['register_error'] = 'Une erreur est survenue. Veuillez réessayer.';
            header('Location: /tomtroc/auth/register');
            exit;
        }
    }

    /**
     * Déconnecte l'utilisateur
     * 
     * Détruit toutes les variables de session, supprime le cookie de session,
     * termine la session en cours et redirige l'utilisateur vers la page d'accueil.
     * 
     * @return void
     */
    public function logout()
    {
        // Détruire toutes les variables de session
        $_SESSION = [];

        // Supprimer le cookie de session s'il existe
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        // Détruire la session complètement
        session_destroy();

        // Rediriger vers la page d'accueil
        header('Location: /tomtroc/');
        exit;
    }
}

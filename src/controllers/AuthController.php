<?php

class AuthController extends Controller
{
    public function login()
    {
        // Se o usuário já está conectado, redirecionar para a home
        if (isset($_SESSION['user'])) {
            header('Location: /tomtroc/');
            exit;
        }

        $pageTitle = 'Connexion - Tom Troc';
        $error = $_SESSION['login_error'] ?? null;
        $success = $_SESSION['login_success'] ?? null;
        unset($_SESSION['login_error']);
        unset($_SESSION['login_success']);

        $this->render('auth/login', [
            'pageTitle' => $pageTitle,
            'error' => $error,
            'success' => $success
        ]);
    }

    public function register()
    {
        // Se o usuário já está conectado, redirecionar para a home
        if (isset($_SESSION['user'])) {
            header('Location: /tomtroc/');
            exit;
        }

        $pageTitle = 'Inscription - Tom Troc';
        $error = $_SESSION['register_error'] ?? null;
        $success = $_SESSION['register_success'] ?? null;
        unset($_SESSION['register_error']);
        unset($_SESSION['register_success']);

        $this->render('auth/register', [
            'pageTitle' => $pageTitle,
            'error' => $error,
            'success' => $success
        ]);
    }

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
            // Vérifier les identifiants
            $userModel = new User();
            $user = $userModel->verifyCredentials($email, $password);

            if ($user) {
                // Connexion réussie - créer la session
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
                // Identifiants invalides
                $_SESSION['login_error'] = 'Email ou mot de passe incorrect.';
                header('Location: /tomtroc/auth/login');
                exit;
            }
        } catch (Exception $e) {
            // Erreur de base de données
            error_log("Erreur lors de la connexion: " . $e->getMessage());
            $_SESSION['login_error'] = 'Une erreur est survenue. Veuillez réessayer.';
            header('Location: /tomtroc/auth/login');
            exit;
        }
    }

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
            $userModel = new User();

            // Vérifier si l'email existe déjà
            if ($userModel->emailExists($email)) {
                $_SESSION['register_error'] = 'Cette adresse email est déjà utilisée.';
                header('Location: /tomtroc/auth/register');
                exit;
            }

            // Vérifier si le pseudo existe déjà
            if ($userModel->pseudoExists($pseudo)) {
                $_SESSION['register_error'] = 'Ce pseudo est déjà utilisé.';
                header('Location: /tomtroc/auth/register');
                exit;
            }

            // Créer l'utilisateur
            $userId = $userModel->create([
                'pseudo' => $pseudo,
                'email' => $email,
                'password' => $password
            ]);

            if ($userId) {
                $_SESSION['login_success'] = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
                header('Location: /tomtroc/auth/login');
                exit;
            } else {
                $_SESSION['register_error'] = 'Une erreur est survenue lors de l\'inscription.';
                header('Location: /tomtroc/auth/register');
                exit;
            }
        } catch (Exception $e) {
            error_log("Erro no registro: " . $e->getMessage());
            $_SESSION['register_error'] = 'Une erreur est survenue. Veuillez réessayer.';
            header('Location: /tomtroc/auth/register');
            exit;
        }
    }

    public function logout()
    {
        // Destruir todas as variáveis de sessão
        $_SESSION = [];

        // Destruir o cookie de sessão se existe
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }

        // Destruir a sessão
        session_destroy();

        // Redirecionar para a home
        header('Location: /tomtroc/');
        exit;
    }
}

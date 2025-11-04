<?php

/**
 * Contrôleur de la liste des livres
 * 
 * Gère l'affichage de la liste complète des livres disponibles sur la plateforme,
 * ainsi que la fonctionnalité de recherche de livres (via page et AJAX).
 * Permet aux utilisateurs de parcourir et rechercher des livres à échanger.
 */
class BooksController extends Controller
{
    /**
     * Affiche la page principale de la liste des livres
     * 
     * Récupère tous les livres disponibles ou filtre les résultats selon un terme
     * de recherche si fourni dans l'URL. Formate les données pour l'affichage
     * et transmet les informations à la vue.
     * 
     * @return void
     */
    public function index()
    {
        // Instancier le modèle Book pour accéder aux données
        $bookModel = new Book();
        
        // Vérifier s'il y a un terme de recherche dans les paramètres GET
        $searchTerm = $_GET['search'] ?? '';
        
        if (!empty($searchTerm)) {
            // Effectuer une recherche filtrée selon le terme fourni
            $booksData = $bookModel->search($searchTerm);
            $pageTitle = 'Résultats de recherche : "' . htmlspecialchars($searchTerm) . '"';
        } else {
            // Récupérer tous les livres disponibles sans filtre
            $booksData = $bookModel->findAll();
            $pageTitle = 'Nos livres à l\'échange';
        }
        
        // Formater les données de la base pour les adapter à la vue
        $books = [];
        foreach ($booksData as $bookData) {
            $books[] = [
                'id' => $bookData['id'],
                'title' => $bookData['titre'],
                'author' => $bookData['auteur'],
                'description' => $bookData['description'] ?? '',
                'seller' => $bookData['pseudo'] ?? 'Utilisateur',
                'seller_id' => $bookData['utilisateur_id'],
                'image' => !empty($bookData['photo']) ? $bookData['photo'] : 'default-image.png',
                'available' => (bool) $bookData['disponible']
            ];
        }
        
        // Transmettre les données à la vue
        $this->render('books/index', [
            'books' => $books,
            'pageTitle' => $pageTitle,
            'searchTerm' => $searchTerm
        ]);
    }
    
    /**
     * Recherche de livres via requête AJAX
     * 
     * Point d'entrée API pour effectuer des recherches de livres dynamiques
     * sans rechargement de page. Retourne les résultats au format JSON.
     * Utilisé pour la recherche en temps réel depuis l'interface utilisateur.
     * 
     * @return void
     */
    public function search()
    {
        // Définir l'en-tête de réponse JSON
        header('Content-Type: application/json');
        
        // Récupérer le terme de recherche depuis les paramètres GET
        $searchTerm = $_GET['q'] ?? '';
        
        // Vérifier que le terme de recherche n'est pas vide
        if (empty($searchTerm)) {
            echo json_encode(['success' => false, 'message' => 'Terme de recherche vide']);
            exit;
        }
        
        try {
            // Instancier le modèle et effectuer la recherche
            $bookModel = new Book();
            $booksData = $bookModel->search($searchTerm);
            
            // Formater les résultats pour la réponse JSON
            $books = [];
            foreach ($booksData as $bookData) {
                $books[] = [
                    'id' => $bookData['id'],
                    'title' => $bookData['titre'],
                    'author' => $bookData['auteur'],
                    'seller' => $bookData['pseudo'] ?? 'Utilisateur',
                    'image' => !empty($bookData['photo']) ? $bookData['photo'] : 'default-image.png',
                    'available' => (bool) $bookData['disponible']
                ];
            }
            
            // Retourner les résultats avec succès
            echo json_encode(['success' => true, 'books' => $books]);
        } catch (Exception $e) {
            // Gérer les erreurs et retourner un message d'échec
            echo json_encode(['success' => false, 'message' => 'Erreur de recherche']);
        }
        
        // Terminer l'exécution pour éviter tout contenu supplémentaire
        exit;
    }
}

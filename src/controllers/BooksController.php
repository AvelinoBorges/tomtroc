<?php

class BooksController extends Controller
{
    /**
     * Affiche la page des livres disponibles
     */
    public function index()
    {
        $bookModel = new Book();
        
        // Vérifier s'il y a un terme de recherche
        $searchTerm = $_GET['search'] ?? '';
        
        if (!empty($searchTerm)) {
            // Rechercher des livres en fonction du terme de recherche
            $booksData = $bookModel->search($searchTerm);
            $pageTitle = 'Résultats de recherche : "' . htmlspecialchars($searchTerm) . '"';
        } else {
            // Rechercher tous les livres disponibles
            $booksData = $bookModel->findAll();
            $pageTitle = 'Nos livres à l\'échange';
        }
        
        // Formater les données pour la vue
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
        
        $this->render('books/index', [
            'books' => $books,
            'pageTitle' => $pageTitle,
            'searchTerm' => $searchTerm
        ]);
    }
    
    /**
     * Recherche de livres via AJAX
     */
    public function search()
    {
        header('Content-Type: application/json');
        
        $searchTerm = $_GET['q'] ?? '';
        
        if (empty($searchTerm)) {
            echo json_encode(['success' => false, 'message' => 'Terme de recherche vide']);
            exit;
        }
        
        try {
            $bookModel = new Book();
            $booksData = $bookModel->search($searchTerm);
            
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
            
            echo json_encode(['success' => true, 'books' => $books]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur de recherche']);
        }
        
        exit;
    }
}

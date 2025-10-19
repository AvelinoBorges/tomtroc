<?php
/**
 * Contrôleur pour la page d'accueil
 */

class HomeController extends Controller
{
    /**
     * Page d'accueil
     * 
     * @return void
     */
    public function index(): void
    {
        // Rechercher les 4 derniers livres ajoutés
        $bookModel = new Book();
        $latestBooksData = $bookModel->findLatest(4);
        
        // Formater les données pour la vue
        $latestBooks = [];
        foreach ($latestBooksData as $bookData) {
            $latestBooks[] = [
                'id' => $bookData['id'],
                'title' => $bookData['titre'],
                'author' => $bookData['auteur'],
                'seller' => $bookData['pseudo'] ?? 'Utilisateur',
                'seller_id' => $bookData['utilisateur_id'],
                'image' => $this->formatImagePath($bookData['photo']),
                'available' => (bool) $bookData['disponible']
            ];
        }
        
        // Données à passer à la vue
        $data = [
            'latestBooks' => $latestBooks,
            'pageTitle' => 'TomTroc - Plateforme d\'échange de livres'
        ];
        
        // Affichage de la vue
        $this->render('home/index', $data);
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
}

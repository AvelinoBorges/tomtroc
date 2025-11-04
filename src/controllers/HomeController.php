<?php
/**
 * Contrôleur pour la page d'accueil
 * 
 * Ce contrôleur gère l'affichage de la page d'accueil de TomTroc,
 * incluant la récupération et l'affichage des derniers livres disponibles.
 */

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil
     * 
     * Cette méthode récupère les 4 derniers livres ajoutés à la plateforme
     * et les transmet à la vue pour l'affichage. Les données sont formatées
     * pour correspondre aux besoins de la vue.
     * 
     * @return void
     */
    public function index(): void
    {
        // Instancier le modèle Book pour accéder aux données des livres
        $bookModel = new Book();
        
        // Récupérer les 4 derniers livres ajoutés dans la base de données
        $latestBooksData = $bookModel->findLatest(4);
        
        // Préparer un tableau pour stocker les données formatées des livres
        $latestBooks = [];
        
        // Parcourir chaque livre récupéré et formater ses données
        foreach ($latestBooksData as $bookData) {
            $latestBooks[] = [
                'id' => $bookData['id'],                              // Identifiant unique du livre
                'title' => $bookData['titre'],                        // Titre du livre
                'author' => $bookData['auteur'],                      // Nom de l'auteur
                'seller' => $bookData['pseudo'] ?? 'Utilisateur',     // Pseudo du vendeur (ou valeur par défaut)
                'seller_id' => $bookData['utilisateur_id'],           // Identifiant de l'utilisateur vendeur
                'image' => $this->formatImagePath($bookData['photo']), // Chemin formaté de l'image
                'available' => (bool) $bookData['disponible']         // Statut de disponibilité (converti en booléen)
            ];
        }
        
        // Préparer les données à transmettre à la vue
        $data = [
            'latestBooks' => $latestBooks,                                  // Tableau des derniers livres
            'pageTitle' => 'TomTroc - Plateforme d\'échange de livres'     // Titre de la page
        ];
        
        // Rendre la vue avec les données préparées
        $this->render('home/index', $data);
    }

    /**
     * Formate le chemin de l'image du livre
     * 
     * Cette méthode prend le nom du fichier photo stocké en base de données
     * et le transforme en chemin d'accès complet pour l'affichage.
     * Si aucune photo n'est fournie, retourne le chemin de l'image par défaut.
     * 
     * @param string|null $photo Le nom du fichier photo (peut être null)
     * @return string Le chemin complet de l'image
     */
    private function formatImagePath(?string $photo): string
    {
        // Si aucune photo n'est fournie, retourner l'image par défaut
        if (empty($photo)) {
            return '/tomtroc/public/images/default-image.png';
        }

        // Si le chemin commence déjà par 'books/', ajouter uniquement le préfixe de base
        if (strpos($photo, 'books/') === 0) {
            return '/tomtroc/public/images/' . $photo;
        }

        // Sinon, construire le chemin complet avec le dossier 'books/'
        return '/tomtroc/public/images/books/' . $photo;
    }
}

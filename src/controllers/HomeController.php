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
        // Données à passer à la vue
        $data = [
            'message' => 'Hello World!'
        ];
        
        // Affichage de la vue
        $this->render('home/index', $data);
    }
}

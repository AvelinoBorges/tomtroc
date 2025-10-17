# TomTroc - Plateforme d'échange de livres

## Description
TomTroc est une plateforme d'échange de livres entre particuliers développée en PHP avec le modèle MVC (Modèle-Vue-Contrôleur) et la Programmation Orientée Objet (POO).

## Structure du projet

```
tomtroc/
├── config/              # Fichiers de configuration
│   ├── autoloader.php   # Chargement automatique des classes
│   └── routes.php       # Configuration des routes
├── src/
│   ├── controllers/     # Contrôleurs de l'application
│   │   ├── Controller.php
│   │   └── HomeController.php
│   ├── models/          # Modèles de données
│   │   └── Model.php
│   ├── services/        # Services métier
│   └── views/           # Vues de l'application
│       └── home/
│           └── index.php
├── public/              # Ressources publiques (CSS, JS, images)
├── .htaccess            # Configuration Apache
├── index.php            # Point d'entrée de l'application
└── README.md
```

## Installation

1. Cloner le projet dans le répertoire de votre serveur web (WAMP, XAMPP, etc.)
2. Configurer la base de données dans `src/models/Model.php`
3. Activer le module `mod_rewrite` d'Apache
4. Accéder à l'application via votre navigateur

## Prochaines étapes

- Configuration de la base de données
- Création des modèles pour les utilisateurs et les livres
- Développement des fonctionnalités d'échange
- Mise en place de l'authentification
- Création de l'interface utilisateur

## Technologies utilisées

- PHP 7.4+
- MySQL
- Apache (mod_rewrite)
- Architecture MVC
- Programmation Orientée Objet

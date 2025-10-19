# TomTroc - Plateforme d'Échange de Livres

Une plateforme web moderne permettant aux utilisateurs d'échanger des livres entre particuliers. Développée en PHP avec une architecture MVC (Modèle-Vue-Contrôleur) et la Programmation Orientée Objet (POO).

## Table des Matières

- [Description](#description)
- [Fonctionnalités](#fonctionnalités)
- [Technologies Utilisées](#technologies-utilisées)
- [Structure du Projet](#structure-du-projet)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Base de Données](#base-de-données)
- [Sécurité](#sécurité)
- [Architecture](#architecture)
- [Routes API](#routes-api)
- [Développement](#développement)
- [Licence](#licence)

## Description

TomTroc est une plateforme d'échange de livres entre particuliers qui facilite le partage de lectures. Les utilisateurs peuvent créer un compte, gérer leur bibliothèque personnelle, consulter les livres disponibles et échanger avec d'autres membres de la communauté.

### Objectifs du Projet

- Promouvoir le partage de livres entre particuliers
- Offrir une interface simple et intuitive
- Garantir la sécurité des données des utilisateurs
- Faciliter les échanges entre lecteurs

## Fonctionnalités

### Actuellement Implémentées

**Système d'Authentification**
- Inscription avec validation des données
- Connexion sécurisée avec hash bcrypt
- Déconnexion complète
- Gestion de sessions sécurisées
- Récupération de mot de passe (à venir)

**Gestion de Compte Utilisateur**
- Profil utilisateur avec photo
- Modification des informations personnelles
- Bibliothèque personnelle
- Statistiques des livres

**Gestion de Livres**
- Ajout de livres avec photo
- Édition des informations
- Suppression avec confirmation
- Gestion de disponibilité
- Upload sécurisé des images

**Page d'Accueil**
- Présentation de la plateforme
- Affichage des derniers livres ajoutés
- Navigation intuitive

**Catalogue de Livres**
- Liste de tous les livres disponibles
- Recherche par titre, auteur ou description
- Filtrage par disponibilité
- Page détaillée pour chaque livre

**Profils Publics**
- Consultation des profils des autres utilisateurs
- Visualisation de leur bibliothèque
- Informations de contact

**Système de Messagerie**
- Messagerie privée entre utilisateurs
- Liste des conversations
- Notifications de nouveaux messages
- Interface de chat intuitive

### En Développement

**Système d'Échange**
- Demandes d'échange de livres
- Acceptation/Refus des demandes
- Historique des échanges
- Système de notation (à venir)

## Technologies Utilisées

### Backend
- **PHP 7.4+** - Langage de programmation
- **PDO** - Accès à la base de données
- **Sessions PHP** - Gestion des utilisateurs connectés
- **Password Hashing** - Sécurité des mots de passe (bcrypt)

### Base de Données
- **MySQL 5.7+** - Système de gestion de base de données
- **Prepared Statements** - Protection contre les injections SQL

### Frontend
- **HTML5** - Structure des pages
- **CSS3** - Stylisation et design responsive
- **JavaScript** - Interactivité côté client
- **Fonts**: Playfair Display, Inter

### Serveur Web
- **Apache 2.4+** - Serveur HTTP
- **mod_rewrite** - Réécriture d'URL pour routing

### Architecture
- **MVC** - Pattern architectural Modèle-Vue-Contrôleur
- **POO** - Programmation Orientée Objet
- **Autoloader** - Chargement automatique des classes
- **Routing** - Système de routage personnalisé

## Structure du Projet

```
tomtroc/
├── config/
│   ├── autoloader.php          # Chargement automatique des classes
│   ├── database.php            # Configuration base de données
│   └── routes.php              # Système de routage
│
├── public/
│   ├── css/                    # Feuilles de style
│   │   ├── style.css           # Styles globaux
│   │   ├── home.css            # Page d'accueil
│   │   ├── auth.css            # Authentification
│   │   ├── compte.css          # Compte utilisateur
│   │   ├── book.css            # Page livre
│   │   ├── books.css           # Catalogue
│   │   ├── messages.css        # Messagerie
│   │   ├── user-profile.css    # Profil public
│   │   ├── header.css          # En-tête
│   │   ├── footer.css          # Pied de page
│   │   └── fonts.css           # Typographie
│   │
│   └── images/
│       ├── books/              # Images des livres
│       └── profiles/           # Photos de profil
│
├── src/
│   ├── controllers/            # Contrôleurs MVC
│   │   ├── Controller.php      # Contrôleur de base
│   │   ├── HomeController.php  # Page d'accueil
│   │   ├── AuthController.php  # Authentification
│   │   ├── CompteController.php # Gestion compte
│   │   ├── BookController.php  # Page livre
│   │   ├── BooksController.php # Catalogue
│   │   ├── UserController.php  # Profil public
│   │   ├── MessageController.php     # Message unique
│   │   └── MessagesController.php    # Liste messages
│   │
│   ├── models/                 # Modèles de données
│   │   ├── Model.php           # Modèle de base
│   │   ├── User.php            # Utilisateur
│   │   ├── Book.php            # Livre
│   │   └── Message.php         # Message
│   │
│   ├── services/               # Services métier
│   │   └── Database.php        # Connexion DB
│   │
│   └── views/                  # Vues (templates)
│       ├── layout/
│       │   ├── header.php      # En-tête commune
│       │   └── footer.php      # Pied de page commun
│       ├── home/
│       │   └── index.php       # Page d'accueil
│       ├── auth/
│       │   ├── login.php       # Connexion
│       │   └── register.php    # Inscription
│       ├── compte/
│       │   ├── index.php       # Mon compte
│       │   ├── add.php         # Ajouter livre
│       │   └── edit.php        # Éditer livre
│       ├── book/
│       │   └── show.php        # Détails livre
│       ├── books/
│       │   └── index.php       # Catalogue
│       ├── user/
│       │   └── profile.php     # Profil public
│       └── messages/
│           └── index.php       # Messagerie
│
├── .htaccess                   # Configuration Apache
├── index.php                   # Point d'entrée
└── README.md                   # Documentation

```

## Installation

### Prérequis

- **Serveur local**: WAMP, XAMPP, MAMP ou équivalent
- **PHP**: Version 7.4 ou supérieure
- **MySQL**: Version 5.7 ou supérieure
- **Apache**: Module `mod_rewrite` activé
- **Extensions PHP**: PDO, PDO_MySQL

### Étapes d'Installation

1. **Cloner ou télécharger le projet**
   ```powershell
   cd C:\wamp64\www
   git clone https://github.com/AvelinoBorges/tomtroc.git
   ```

2. **Configurer Apache**
   - Vérifier que `mod_rewrite` est activé
   - Vérifier que `.htaccess` est pris en compte (AllowOverride All)

3. **Créer la base de données**
   - Ouvrir phpMyAdmin: `http://localhost/phpmyadmin`
   - Créer une nouvelle base de données nommée `tomtroc`
   - Importer le schéma SQL (si disponible) ou laisser l'application créer les tables

4. **Configurer la connexion à la base de données**
   - Modifier le fichier `config/database.php` si nécessaire
   - Valeurs par défaut:
     - Host: `localhost`
     - Database: `tomtroc`
     - Username: `root`
     - Password: `` (vide)

5. **Configurer les permissions**
   ```powershell
   # Donner les droits d'écriture aux dossiers d'upload
   icacls "C:\wamp64\www\tomtroc\public\images\books" /grant Everyone:F
   icacls "C:\wamp64\www\tomtroc\public\images\profiles" /grant Everyone:F
   ```

6. **Accéder à l'application**
   ```
   http://localhost/tomtroc
   ```

## Configuration

### Base de Données

Fichier: `config/database.php`

```php
<?php
return [
    'host' => 'localhost',
    'dbname' => 'tomtroc',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];
```

### Environnement de Développement

Dans `index.php`, les erreurs sont activées par défaut:

```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

**Pour la production**, désactiver l'affichage des erreurs:

```php
ini_set('display_errors', 0);
error_reporting(0);
```

## Utilisation

### Création de Compte

1. Accéder à: `http://localhost/tomtroc/auth/register`
2. Remplir le formulaire d'inscription:
   - Pseudo (unique)
   - Email (unique)
   - Mot de passe (minimum 6 caractères)
   - Nom et prénom (optionnels)
3. Cliquer sur "S'inscrire"

### Connexion

1. Accéder à: `http://localhost/tomtroc/auth/login`
2. Saisir email et mot de passe
3. Cliquer sur "Se connecter"

### Gérer son Compte

1. Cliquer sur "Mon Compte" dans le menu
2. URL: `http://localhost/tomtroc/compte`
3. Fonctionnalités disponibles:
   - Modifier ses informations personnelles
   - Changer sa photo de profil
   - Voir sa bibliothèque
   - Ajouter/Éditer/Supprimer des livres

### Ajouter un Livre

1. Aller dans "Mon Compte"
2. Cliquer sur "Ajouter un livre"
3. Remplir le formulaire:
   - Titre (obligatoire)
   - Auteur (obligatoire)
   - Description
   - Photo de couverture
   - Disponibilité
4. Cliquer sur "Ajouter"

### Consulter le Catalogue

1. Cliquer sur "Nos Livres" dans le menu
2. URL: `http://localhost/tomtroc/books`
3. Fonctionnalités:
   - Voir tous les livres disponibles
   - Rechercher par titre, auteur ou description
   - Cliquer sur un livre pour voir les détails

### Voir un Profil Utilisateur

1. Cliquer sur le nom d'un utilisateur
2. URL: `http://localhost/tomtroc/user/profile/{id}`
3. Informations affichées:
   - Photo et informations du membre
   - Date d'inscription
   - Bibliothèque publique
   - Bouton pour envoyer un message

### Envoyer un Message

1. Accéder au profil d'un utilisateur
2. Cliquer sur "Envoyer un message"
3. Rédiger et envoyer le message

### Consulter ses Messages

1. Cliquer sur "Messagerie" dans le menu
2. URL: `http://localhost/tomtroc/messages`
3. Voir la liste des conversations
4. Cliquer sur une conversation pour voir les messages

## Base de Données

### Schéma des Tables

#### Table `utilisateurs`
Stocke les informations des utilisateurs.

```sql
CREATE TABLE utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pseudo VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    photo VARCHAR(255),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    actif TINYINT(1) DEFAULT 1
);
```

#### Table `livres`
Stocke les informations des livres.

```sql
CREATE TABLE livres (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    description TEXT,
    photo VARCHAR(255),
    disponible TINYINT(1) DEFAULT 1,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
);
```

#### Table `messages`
Stocke les messages entre utilisateurs.

```sql
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    contenu TEXT NOT NULL,
    lu TINYINT(1) DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id)
);
```

#### Table `echanges` (À implémenter)
Stocke les demandes d'échange.

```sql
CREATE TABLE echanges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    livre_id INT NOT NULL,
    demandeur_id INT NOT NULL,
    statut ENUM('en_attente', 'accepte', 'refuse', 'termine') DEFAULT 'en_attente',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (livre_id) REFERENCES livres(id),
    FOREIGN KEY (demandeur_id) REFERENCES utilisateurs(id)
);
```

## Sécurité

### Mesures de Sécurité Implémentées

**Authentification**
- Hash des mots de passe avec `password_hash()` (bcrypt)
- Vérification avec `password_verify()`
- Sessions sécurisées avec identifiants uniques
- Déconnexion complète avec destruction de session

**Protection SQL Injection**
- Utilisation exclusive de Prepared Statements
- Binding de paramètres avec PDO
- Pas de concaténation SQL

**Protection XSS**
- Échappement HTML avec `htmlspecialchars()` dans toutes les vues
- Validation des entrées utilisateur
- Sanitisation des données

**Upload de Fichiers**
- Vérification du type MIME
- Limitation de la taille des fichiers
- Renommage sécurisé des fichiers
- Stockage hors du code source

**Contrôle d'Accès**
- Vérification d'authentification sur pages protégées
- Vérification de propriété pour édition/suppression
- Sessions avec timeout
- Protection CSRF (à implémenter)

**Validation des Données**
- Validation côté serveur
- Vérification des formats (email, etc.)
- Nettoyage des entrées utilisateur

### Bonnes Pratiques

```php
// Toujours échapper les données en sortie
echo htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

// Toujours utiliser des prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// Toujours hasher les mots de passe
$hash = password_hash($password, PASSWORD_DEFAULT);
```

## Architecture

### Pattern MVC

**Modèle (Model)**
- Gère la logique métier et l'accès aux données
- Interaction avec la base de données
- Validation des données
- Exemples: `User.php`, `Book.php`, `Message.php`

**Vue (View)**
- Présentation des données
- Templates HTML/CSS
- Pas de logique métier
- Exemples: `home/index.php`, `books/index.php`

**Contrôleur (Controller)**
- Gère les requêtes HTTP
- Coordonne Modèle et Vue
- Traite les données de formulaire
- Exemples: `HomeController.php`, `BookController.php`

### Flux de Requête

```
1. URL: /books/index
2. .htaccess → index.php
3. routes.php analyse l'URL
4. Instanciation de BooksController
5. Appel de la méthode index()
6. Controller récupère les données (Model)
7. Controller passe les données à la Vue
8. Vue génère le HTML
9. Réponse envoyée au navigateur
```

### Autoloading

Le fichier `config/autoloader.php` charge automatiquement les classes:

```php
spl_autoload_register(function ($class) {
    $file = ROOT . DS . 'src' . DS . 'controllers' . DS . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
        return;
    }
    // ... autres répertoires
});
```

## Routes API

### Authentification

| Méthode | URL | Description | Auth |
|---------|-----|-------------|------|
| GET | `/auth/login` | Page de connexion | Non |
| POST | `/auth/processLogin` | Traitement connexion | Non |
| GET | `/auth/register` | Page d'inscription | Non |
| POST | `/auth/processRegister` | Traitement inscription | Non |
| GET | `/auth/logout` | Déconnexion | Oui |

### Compte Utilisateur

| Méthode | URL | Description | Auth |
|---------|-----|-------------|------|
| GET | `/compte` | Page mon compte | Oui |
| POST | `/compte/updateProfile` | Mise à jour profil | Oui |
| GET | `/compte/add` | Formulaire ajout livre | Oui |
| POST | `/compte/processAdd` | Traitement ajout livre | Oui |
| GET | `/compte/editBook/{id}` | Formulaire édition livre | Oui |
| POST | `/compte/processEdit/{id}` | Traitement édition | Oui |
| GET | `/compte/deleteBook/{id}` | Suppression livre | Oui |

### Livres

| Méthode | URL | Description | Auth |
|---------|-----|-------------|------|
| GET | `/` ou `/home` | Page d'accueil | Non |
| GET | `/books` | Catalogue de livres | Non |
| GET | `/book/show/{id}` | Détails d'un livre | Non |
| GET | `/books/search?q={term}` | Recherche de livres | Non |

### Utilisateurs

| Méthode | URL | Description | Auth |
|---------|-----|-------------|------|
| GET | `/user/profile/{id}` | Profil public | Non |

### Messagerie

| Méthode | URL | Description | Auth |
|---------|-----|-------------|------|
| GET | `/messages` | Liste conversations | Oui |
| GET | `/messages/conversation/{id}` | Conversation avec user | Oui |
| POST | `/messages/send` | Envoyer message | Oui |

## Développement

### Environnement de Développement

**Configuration recommandée:**
- Visual Studio Code avec extensions PHP
- WAMP/XAMPP local
- Git pour le versionnage
- phpMyAdmin pour la gestion de la base de données

### Ajouter un Nouveau Contrôleur

1. Créer le fichier dans `src/controllers/`
2. Étendre la classe `Controller`
3. Implémenter les méthodes nécessaires
4. Le routing est automatique

Exemple:

```php
<?php
class MonNouveauController extends Controller
{
    public function index(): void
    {
        $this->render('mon_nouveau/index', [
            'title' => 'Ma Page'
        ]);
    }
}
```

### Ajouter un Nouveau Modèle

1. Créer le fichier dans `src/models/`
2. Étendre la classe `Model`
3. Définir la propriété `$table`
4. Implémenter les méthodes métier

Exemple:

```php
<?php
class MonModele extends Model
{
    protected string $table = 'ma_table';
    
    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->getPdo()->query($sql);
        return $stmt->fetchAll();
    }
}
```

### Debugging

**Afficher les erreurs:**
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

**Afficher les requêtes SQL:**
```php
echo $stmt->debugDumpParams();
```

**Logs personnalisés:**
```php
error_log("Message de debug");
```

### Tests

Actuellement, le projet n'a pas de tests automatisés.

**À implémenter:**
- Tests unitaires avec PHPUnit
- Tests d'intégration
- Tests fonctionnels

## Roadmap

### Version 1.0 - MVP (Minimum Viable Product)

**Objectif:** Implémenter les fonctionnalités principales permettant aux lecteurs de partager et échanger leurs livres.

#### Fonctionnalités Principales du MVP

**1. Inscription et Connexion**
- [x] Inscription directe sans validation par email
- [x] Connexion sécurisée
- [x] Gestion des sessions
- [x] Hash des mots de passe

**2. Page de Profil Utilisateur**
- [x] Modification de son propre profil
- [x] Consultation des profils des autres utilisateurs
- [x] Photo de profil
- [x] Informations personnelles (nom, prénom, pseudo)
- Note: Pas de liste complète des utilisateurs (mise en relation via la bibliothèque uniquement)

**3. Bibliothèque Personnelle (Page "Mon compte")**
- [x] Création de livres avec:
  - [x] Titre (obligatoire)
  - [x] Auteur (champ texte simple)
  - [x] Image de couverture (optionnelle)
  - [x] Description (texte long)
  - [x] Statut de disponibilité
- [x] Modification des livres
- [x] Suppression des livres
- [x] Visualisation de sa bibliothèque

**4. Page "Nos livres à l'échange"**
- [x] Liste des livres disponibles à l'échange
- [x] Recherche par titre de livre
- [x] Affichage des informations essentielles
- [ ] Amélioration: Recherche par auteur et description

**5. Détail d'un Livre**
- [x] Page de détail complète du livre
- [x] Lien vers le profil du propriétaire
- [x] Bouton pour envoyer un message au propriétaire
- [x] Informations complètes (titre, auteur, description, photo)

**6. Système de Messagerie**
- [x] Consultation de la liste des messages reçus
- [x] Visualisation d'un fil de discussion
- [x] Envoi de messages
- [x] Réponse aux messages
- [x] Indication des messages lus/non lus

#### Fonctionnalités Complémentaires Implémentées

- [x] Page d'accueil publique
- [x] Architecture MVC complète
- [x] Sécurité (SQL injection, XSS)
- [x] Upload sécurisé d'images

#### Non Requis pour le MVP (Laissé en suspens)

- [ ] Partie administration (modération utilisateurs/livres)
- [x] Version responsive 

### Version 1.1 (Améliorations Futures)

**Fonctionnalités d'Échange Avancées**
- [ ] Système de demandes d'échange formelles
- [ ] Statuts d'échange (en attente, accepté, refusé, terminé)
- [ ] Historique des échanges
- [ ] Notifications d'échange

**Amélioration de la Recherche**
- [ ] Recherche multi-critères (titre + auteur + description)
- [ ] Filtres avancés (disponibilité, date d'ajout)
- [ ] Pagination des résultats
- [ ] Tri des résultats

**Messagerie Améliorée**
- [ ] Notifications en temps réel
- [ ] Mise en forme des messages (gras, italique)
- [ ] Pièces jointes
- [ ] Suppression de messages
- [ ] Archivage des conversations

**Profils Enrichis**
- [ ] Statistiques utilisateur (nombre d'échanges, livres possédés)
- [ ] Système de notation/avis
- [ ] Historique public des échanges
- [ ] Liste de souhaits (wishlist)

### Version 2.0 (Administration et Modération)

**Interface Administrateur**
- [ ] Tableau de bord admin
- [ ] Modération des utilisateurs
- [ ] Modération des livres
- [ ] Gestion des signalements
- [ ] Statistiques globales de la plateforme

**Sécurité Avancée**
- [ ] Validation par email à l'inscription
- [ ] Double authentification (2FA)
- [ ] Récupération de mot de passe
- [ ] Système de signalement d'abus
- [ ] Logs d'activité

### Version 3.0 (Fonctionnalités Avancées)

**Gestion Avancée des Livres**
- [ ] Table auteurs séparée (nom, prénom, pseudo)
- [ ] API externe pour récupérer infos livres (ISBN)
- [ ] Catégories et genres
- [ ] Tags personnalisés
- [ ] Notes et critiques

**Expérience Utilisateur**
- [ ] Système de recommandations
- [ ] Suggestions de livres basées sur les goûts
- [ ] Liste de lecture partagée
- [ ] Groupes de lecture
- [ ] Événements et rencontres

**Technique**
- [ ] API RESTful complète
- [ ] Application mobile (iOS/Android)
- [ ] Mode hors ligne
- [ ] Export/Import de données
- [ ] Intégration réseaux sociaux

### Statut Actuel

**Version:** 1.0 MVP - Complétée à 100%  
**Toutes les fonctionnalités principales du MVP sont implémentées et fonctionnelles.**

Le projet est prêt pour la mise en production et peut être utilisé par les lecteurs pour échanger leurs livres. Les prochaines versions apporteront des améliorations et des fonctionnalités supplémentaires basées sur les retours utilisateurs.

## Contribution

Ce projet est actuellement un projet éducatif personnel. Les contributions ne sont pas acceptées pour le moment.

## Auteur

**Avelino Borges**
- GitHub: [@AvelinoBorges](https://github.com/AvelinoBorges)

## Licence

Ce projet est un projet éducatif développé dans le cadre d'une formation. Tous droits réservés.

## Remerciements

- OpenClassrooms pour le parcours de formation
- La communauté PHP pour la documentation
- Les contributeurs open-source des outils utilisés

---

**Version:** 1.0.0  
**Dernière mise à jour:** 20 octobre 2025  
**Statut:** En développement actif

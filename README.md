# KaijuHunter
Projet Symfony — Gestion d’inventaire de figures

---

## Description
KaijuHunter est une application web Symfony 6 / Doctrine ORM pour gérer des figures rangées dans des vitrines :

- Figure : name (string), imageName (string), relation vitrine (ManyToOne), participation à des arenas (ManyToMany)
- Vitrine : description (string/texte), collection figures (OneToMany), propriétaire (Member)
- Arena : description, flag publie (bool), propriétaire (Member), collection de figures (ManyToMany)

L’application intègre maintenant :
- un système complet d’authentification (login/logout, rôles)  
- rôles : `ROLE_USER` + `ROLE_ADMIN`  
- un accès contextuel aux données selon l’utilisateur connecté  
- un CRUD contextualisé (Figures → Vitrine / Arenas → Member)  
- un contrôle d’accès aux éléments privés (arenas non publiées, vitrines d’un utilisateur, etc.)  
- un upload d’images pour les Figures  
- un jeu de Fixtures complet incluant un admin + deux utilisateurs réels

Cette étape intègre Bootstrap via la distribution Start Bootstrap – Shop Homepage et met en place des gabarits Twig minimalistes.

---

## Prérequis
- PHP compatible Symfony (≥ 8.1 recommandé)
- Composer
- Symfony CLI (recommandé)
- Base de données configurée (SQLite en dev possible)
- Optionnel : Migrations/fixtures prêtes si besoin
- Extension fileinfo activée (pour upload d’images)

---

## Structure actuelle (principaux fichiers)

### **Contrôleurs**
- `src/Controller/HomeController.php` → route home (/)  
- `src/Controller/SecurityController.php` → login/logout  
- `src/Controller/FigureController.php` → CRUD + image upload + contextualisation Vitrine  
- `src/Controller/VitrineController.php` → vitrine_index (/vitrines), vitrine_show (/vitrines/{id})  
- `src/Controller/ArenaController.php` → index/show/new/edit/delete + contrôle d’accès

### **Gabarits Twig**
- `templates/base.html.twig` (liens CSS/JS, menu, login/logout, flash messages)  
- `templates/security/login.html.twig`  
- `templates/home/index.html.twig`  
- `templates/figure/index.html.twig`, `templates/figure/show.html.twig`  
- `templates/vitrine/index.html.twig`, `templates/vitrine/show.html.twig`  
- `templates/arena/index.html.twig`, `templates/arena/show.html.twig`, `templates/arena/figure_show.html.twig`

### **Intégration Start Bootstrap**
- `public/startbootstrap-shop-homepage-gh-pages/` (css/styles.css, js/scripts.js, etc.)
- `config/packages/framework.yaml` → `assets.base_path: '/startbootstrap-shop-homepage-gh-pages'`

### **Menu Bootstrap**
- `config/packages/bootstrap_menu.yaml`
- Bundle : `camurphy/bootstrap-menu-bundle`

### **Fixtures**
- `AppFixtures.php` → 1 admin + 2 utilisateurs + vitrines + figures + arenas (publiées & privées)

---

## Installation et exécution

### 1) Dépendances
    composer install

### 2) Base de données (développement)
    symfony console doctrine:database:create
    symfony console doctrine:schema:create
    # ou via migrations :
    # symfony console doctrine:migrations:migrate
    # Charger les fixtures :
    symfony console doctrine:fixtures:load

### 3) Intégrer Start Bootstrap (conformément au guide)
Depuis la racine du projet :

    cd public
    wget https://github.com/startbootstrap/startbootstrap-shop-homepage/archive/gh-pages.zip
    unzip gh-pages.zip
    # → crée public/startbootstrap-shop-homepage-gh-pages/

Configurer Symfony pour pointer les assets du thème :

config/packages/framework.yaml

    framework:
      assets:
        base_path: '/startbootstrap-shop-homepage-gh-pages'

### 4) Menu Bootstrap
Installer le bundle :

    symfony composer require camurphy/bootstrap-menu-bundle

Configuration minimale :

config/packages/bootstrap_menu.yaml

    bootstrap_menu:
      menus:
        main:
          items:
            home:     { label: 'Accueil', route: 'home' }
            figures:  { label: 'Figures', route: 'app_figure_index' }
            vitrines: { label: 'Vitrines', route: 'vitrine_index' }
            arenas:   { label: 'Arenas', route: 'app_arena_index' }

### 5) Lancer le serveur

    symfony server:start
    # ou :
    symfony serve -d

Ouvrir :
- / (Accueil)
- /login
- /vitrines
- /arena
- /figure

---

## Étapes réalisées
- [x] Squelette Symfony + Doctrine (SQLite dev)  
- [x] Entités : Figure, Vitrine (owner), Member, Arena (publie + owner + figures)  
- [x] Fixtures complètes (admin, olivier, slash + vitrines + figures + arenas)  
- [x] Contrôleurs : Home / Figure / Vitrine / Arena avec routes nommées  
- [x] Twig : base.html.twig + pages home, figure, vitrine, arena  
- [x] Bootstrap via Start Bootstrap – Shop Homepage  
- [x] Configuration assets : framework.assets.base_path  
- [x] Menu Bootstrap (bundle + bootstrap_menu.yaml)  
- [x] Upload d’images pour les Figures  
- [x] CRUD contextualisé (Figures → Vitrine / Arenas → Member)  
- [x] Redirections automatiques cohérentes (vers vitrine/owner)  
- [x] Authentification (login/logout, hashing, provider Member)  
- [x] Rôles : `ROLE_USER`, `ROLE_ADMIN`  
- [x] Contrôle d’accès (admin / owner / public)  
- [x] Filtrage automatique selon utilisateur (vitrines, figures, arenas)  
- [x] Accès aux arenas privées réservé au propriétaire ou à l’admin  

---

## Prochaines étapes
- [ ] Améliorer l’organisation visuelle + UX (cards, grids, dashboard utilisateur)  
- [ ] Pagination et recherche  
- [ ] Validation entités + messages d’erreur  
- [ ] Personnalisation légère du CSS  
- [ ] Utiliser les Voters pour simplifier la logique d’accès  

---

## Dépannage rapide

- **Route introuvable**  
      bin/console debug:router | grep -E 'home|figure|vitrine|arena'

- **Classe contrôleur non trouvée**  
  Vérifier namespace `App\Controller` et que le nom de classe = nom du fichier.

- **Template introuvable**  
  Vérifier le chemin `templates/...` et l’appel `return $this->render('...')`.

- **Page blanche**  
  Contrôler les blocs Twig ({% block body %}), vider le cache :  
  bin/console cache:clear

- **Erreur d’accès (403)**  
  Vérifier :  
  - connexion utilisateur  
  - rôle `ROLE_ADMIN` ou ownership  
  - conditions `isGranted()` dans les contrôleurs  

---

## Auteur
Projet pédagogique (CSC4101/CSC4102).  
Développé par Ismail Abid — Encadrant : Olivier Berger.

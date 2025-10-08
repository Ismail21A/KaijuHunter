# KaijuHunter
Projet Symfony — Gestion d’inventaire de figures

---

## Description
KaijuHunter est une application web Symfony 6 / Doctrine ORM pour gérer des figures rangées dans des vitrines :

- Figure : name (string), relation vitrine (ManyToOne).
- Vitrine : description (string/texte), collection figures (OneToMany).

Cette étape intègre Bootstrap via la distribution Start Bootstrap – Shop Homepage et met en place des gabarits Twig minimalistes (sans images, uniquement les champs réels).

---

## Prérequis
- PHP compatible Symfony (≥ 8.1 recommandé)
- Composer
- Symfony CLI (recommandé)
- Base de données configurée (SQLite en dev possible)
- Optionnel : Migrations/fixtures prêtes si besoin

---

## Structure actuelle (principaux fichiers)
- Contrôleurs
  - src/Controller/HomeController.php → route home (/)
  - src/Controller/FigureController.php → figure_index (/figures),    figure_show (/figures/{id})
  - src/Controller/VitrineController.php → vitrine_index (/vitrines), vitrine_show (/vitrines/{id})
- Gabarits Twig
  - templates/base.html.twig (liens CSS/JS du thème via asset(), blocs menu et body)
  - templates/home/index.html.twig
  - templates/figure/index.html.twig, templates/figure/show.html.twig
  - templates/vitrine/index.html.twig, templates/vitrine/show.html.twig
- Intégration Start Bootstrap
  - public/startbootstrap-shop-homepage-gh-pages/ (fichiers css/styles.css, js/scripts.js, etc.)
  - config/packages/framework.yaml → assets.base_path: '/startbootstrap-shop-homepage-gh-pages'
- Menu Bootstrap
  - config/packages/bootstrap_menu.yaml (menu main)
  - Bundle : camurphy/bootstrap-menu-bundle installé

---

## Installation et exécution

### 1) Dépendances
    composer install

### 2) Base de données (développement)
    symfony console doctrine:database:create
    symfony console doctrine:schema:create
    # ou via migrations :
    # symfony console doctrine:migrations:migrate
    # (facultatif) charger fixtures :
    # symfony console doctrine:fixtures:load

### 3) Intégrer Start Bootstrap (conformément au guide)
Depuis la racine du projet :

    cd public
    wget https://github.com/startbootstrap/startbootstrap-shop-homepage/archive/gh-pages.zip
    unzip gh-pages.zip
    # → crée public/startbootstrap-shop-homepage-gh-pages/

Configurer Symfony pour pointer les assets du thème :

config/packages/framework.yaml

    framework:
      # ...
      assets:
        base_path: '/startbootstrap-shop-homepage-gh-pages'

Grâce à assets.base_path, dans base.html.twig les appels {{ asset('css/styles.css') }} et {{ asset('js/scripts.js') }} pointent vers le dossier du thème.

### 4) Menu Bootstrap
Installer le bundle :

    symfony composer require camurphy/bootstrap-menu-bundle

Configuration minimale :

config/packages/bootstrap_menu.yaml

    bootstrap_menu:
      menus:
        main:
          items:
            home:      { label: 'Accueil',   route: 'home' }
            figures: { label: 'Figures', route: 'figure_index' }
            vitrines:  { label: 'Vitrines',  route: 'vitrine_index' }

### 5) Lancer le serveur

    symfony server:start
    # ou
    symfony serve -d

Ouvrir :
- / (Accueil)
- /figures
- /vitrines

---

## Étapes réalisées
- [x] Squelette Symfony + Doctrine (SQLite dev)
- [x] Entités : Figure (name, vitrine), Vitrine (description, figurines)
- [x] Fixtures de base (si présentes)
- [x] Contrôleurs : Home / Figure / Vitrine avec routes nommées
- [x] Twig : base.html.twig + pages home, figure (index/show), vitrine (index/show)
- [x] Bootstrap via Start Bootstrap – Shop Homepage (téléchargé dans public/…)
- [x] Configuration assets : framework.assets.base_path
- [x] Menu Bootstrap (bundle installé + bootstrap_menu.yaml minimal)

---

## Prochaines étapes
- [ ] Formulaires (création/édition) pour Figure/Vitrine (FormType, contrôleurs POST)
- [ ] Pagination et recherche (liste des figurines)
- [ ] Validation entités + messages d’erreur
- [ ] Personnalisation légère du CSS (surcouches au thème)
- [ ] Optionnel : Authentification/autorisation si demandé par le guide

---

## Dépannage rapide
- Route introuvable  
  bin/console debug:router | grep -E 'home|figure|vitrine'
- Classe contrôleur non trouvée  
  Vérifier namespace App\Controller; et que le nom de classe = nom du fichier.
- Template introuvable  
  Vérifier le chemin templates/... et l’appel return $this->render('...').
- Page blanche  
  Contrôler les blocs Twig ({% block body %}), vider le cache :  
  bin/console cache:clear

---

## Auteur
Projet pédagogique (CSC4101/CSC4102).  
Développé par Ismail Abid — Encadrant : Olivier Berger.
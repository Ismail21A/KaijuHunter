# KaijuHunter
Projet Symfony — Gestion d’inventaire de figures (Solo Leveling × Kaiju N°8)

---

## Description
KaijuHunter est une application web Symfony permettant aux membres d’une communauté de :
- gérer un inventaire privé de figures,
- publier certaines figures dans des galeries publiques,
- consulter les collections des autres membres.

Le projet s’appuie sur **Symfony 6** et **Doctrine ORM** avec une base SQLite en développement.

---

## Structure actuelle
- `src/Controller/FigureController.php` → premier contrôleur Web
- `templates/figure/index.html.twig` → gabarit Twig de test
- `todo.sqlite` → base SQLite locale (tests/dev)
- `.gitignore` → ignore `vendor/`, `var/`, `.env.local`…

---

## État d’avancement

### Étapes terminées
- [x] Création du dépôt GitHub + configuration branche `main`
- [x] Ajout d’un `.gitignore` adapté
- [x] Mise en place de la structure Symfony (squelette `todo`)
- [x] Tests avec base SQLite + Doctrine ORM
- [x] Création d’un contrôleur web (`FigureController`)
- [x] Vérification de la route `/figures` (affichage basique Twig)

### Prochaines étapes
- [ ] Créer l’entité **Figure** avec Doctrine (`make:entity Figure`)
  - Attributs : `id`, `nom`, `description`, `date_ajout`, `photo` (optionnelle)
- [ ] Générer et exécuter une migration (`make:migration` + `doctrine:migrations:migrate`)
- [ ] Préparer des **fixtures** pour insérer quelques figurines de test
- [ ] Adapter le contrôleur :
  - [ ] Récupérer toutes les figurines depuis la base
  - [ ] Passer la liste à Twig (`index.html.twig`)
- [ ] Modifier `templates/figure/index.html.twig` pour afficher la liste réelle
- [ ] Vérifier affichage dans le navigateur
- [ ] Améliorer la présentation (Twig, CSS, Bootstrap ou équivalent)

---

## Installation & Exécution

### 1. Cloner le projet
```bash
git clone git@github.com:Ismail21A/KaijuHunter.git
cd KaizoKage
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Créer la base SQLite (dev)

```bash
symfony console doctrine:database:create
symfony console doctrine:schema:create
```

### 4. Lancer le serveur local

```bash
symfony server:start
```
Puis ouvrir : http://127.0.0.1:8000/figures

---

## Auteur

Projet pédagogique réalisé dans le cadre du module CSC4101 / CSC4102 @ Télécom SudParis.
Développé par Ismail Abid.
Encadrant : Olivier Berger.
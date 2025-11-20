# KaijuHunter

KaijuHunter est une application Symfony permettant de gérer une collection de **figures** rangées dans une **vitrine personnelle**, et de créer des **arenas** (publiques ou privées) qui regroupent certaines figures.
L’univers s’inspire librement de Kaiju No. 8 et Solo Leveling.

L’objectif pédagogique est de couvrir :

* la modélisation de données avec Doctrine (relations 1–N et N–N),
* les opérations CRUD contextualisées,
* l’authentification / autorisations,
* l’upload d’images,
* un frontend cohérent basé sur Bootstrap.

---

## 1. Modèle fonctionnel

### 1.1. Membre (`Member`)

Représente un utilisateur de l’application.

Champs principaux :

* `id`
* `email`
* `password`
* `vitrine` : vitrine personnelle (relation OneToOne vers `Vitrine`)
* `arenas` : arenas qu’il a créées (OneToMany)

Particularités :

* un membre se connecte avec `email` + `mot de passe`,
* chaque membre possède **une seule vitrine**,
* les droits (lecture / écriture) sur vitrine, figures et arenas dépendent de ce membre et de ses rôles,
* l’admin a tous les droits sur toutes les données.

### 1.2. Vitrine / Inventaire (`Vitrine`)

Une vitrine (inventaire) est l’espace personnel où un **membre** range ses figures.

Champs principaux :

* `id`
* `description`
* `owner` : membre propriétaire (relation OneToOne vers `Member`)
* `figures` : figures contenues dans cette vitrine (OneToMany vers `Figure`)
* éventuelle image optionnelle selon l’implémentation (`imageFilename`)

Règles d’accès :

* un membre ne peut voir et modifier **que sa propre vitrine**,
* un administrateur (`ROLE_ADMIN`) peut voir / modifier toutes les vitrines.

### 1.3. Figure / Objet (`Figure`)

Une figure est un objet de collection appartenant à une vitrine.

Champs principaux :

* `id`
* `name`
* `vitrine` : vitrine à laquelle la figure appartient (ManyToOne)
* `arenas` : arenas dans lesquelles la figure apparaît (ManyToMany)
* `imageName` : fichier image de la figure (upload)

Contraintes :

* chaque figure appartient à **une seule vitrine**,
* une figure peut apparaître dans **plusieurs arenas**.

### 1.4. Arena (`Arena`)

Une arena est un regroupement de figures, appartenant à un membre, et pouvant être :

* **publique** (visible par tous),
* **privée** (visible uniquement par son créateur et l’admin).

Champs principaux :

* `id`
* `description`
* `publie` (booléen)
* `owner` : membre créateur
* `figures` : liste de figures associées (ManyToMany)

Règles d’accès :

* publique → visible par tous (y compris non connectés),
* privée → visible par le créateur ou un admin,
* édition / suppression → réservée au créateur ou à l’admin.

---

## 2. Installation locale

### 2.1. Prérequis

* PHP 8.1+
* Composer
* Symfony CLI (recommandé)
* SQLite (par défaut) ou un autre SGBD compatible Doctrine
* Node / npm (optionnel si tu veux recompiler du front custom)

### 2.2. Cloner le projet

```bash
git clone https://github.com/Ismail21A/KaijuHunter.git
cd KaijuHunter
```

### 2.3. Installer les dépendances PHP

```bash
composer install
```

### 2.4. Configuration de l’environnement

Créer un fichier `.env.local` à la racine, au minimum pour la base de données, par exemple :

```env
APP_ENV=dev
APP_DEBUG=1
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

Tu peux aussi configurer un autre SGBD (MySQL/PostgreSQL) en adaptant `DATABASE_URL`.

### 2.5. Créer la base de données et appliquer les migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 2.6. Charger les données de test (fixtures)

Les fixtures créent :

* plusieurs membres (dont un admin),
* leurs vitrines associées,
* plusieurs figures,
* des arenas publiques et privées.

Commande :

```bash
php bin/console doctrine:fixtures:load
```

Confirmer l’écrasement des données si demandé.

### 2.7. Lancer le serveur Symfony

```bash
symfony serve
```

L’application est alors accessible sur :

* `http://127.0.0.1:8000`

---

## 3. Navigation dans l’interface

### 3.1. Barre de navigation principale

En haut de chaque page, la navbar propose :

* **Accueil** → route `home`
* **Figures** → route `app_figure_index`
* **Vitrine** → route `vitrine_show` (vitrine du membre connecté)
* **Arenas** → route `app_arena_index`

À droite, selon l’état de connexion :

* Si **non connecté** :

  * bouton **Connexion** → route `app_login`
* Si **connecté** :

  * bouton **Mon espace** → route `app_member_show` (profil du membre connecté)
  * lien **Déconnexion** → route `app_logout`

### 3.2. Page d’accueil (`/`)

* Hero présentant **KaijuHunter**
* Bouton **Explorer les arenas publiques** → `app_arena_index`
* Bouton **Accéder à ma vitrine** → `vitrine_show` (si connecté)
* Rappel des comptes de test

### 3.3. Vitrine

#### Vitrine personnelle — `/vitrine` ou `/vitrines/{id}` (`vitrine_show`)

* Détails de la vitrine (description, éventuelle image, ID)
* Liste des figures de cette vitrine, sous forme de cartes
* Pour le propriétaire ou l’admin :

  * bouton **Ajouter une figure**
  * bouton **Modifier la vitrine**
* Bouton **Retour à l’accueil**

Accès :

* propriétaire ou admin uniquement (un membre ne voit jamais la vitrine d’un autre).

### 3.4. Figures

#### Liste des figures — `/figure` (`app_figure_index`)

* Grille de cartes avec :

  * image de la figure,
  * nom,
  * vitrine associée,
* (éventuel) champ de recherche pour filtrer **par nom**
* Bouton **Nouvelle figure** (si connecté)

Chaque carte propose :

* un accès à la fiche figure,
* un bouton **Modifier** (si autorisé).

#### Détails d’une figure — `/figure/{id}` (`app_figure_show`)

* Image principale
* ID, nom, vitrine associée
* Liste des arenas où apparaît la figure
* Bouton **Modifier** (si propriétaire ou admin)
* Formulaire de suppression (si autorisé)

#### Création / édition de figure

* Formulaire avec :

  * nom,
  * vitrine (fixée à la vitrine du membre, non modifiable ou filtrée),
  * arenas possibles (filtrées selon le propriétaire),
  * upload de l’image (avec contrainte de format/taille),
* Preview de l’image choisie avant enregistrement.

### 3.5. Arenas

#### Liste des arenas — `/arena` (`app_arena_index`)

* Tableau ou cartes listant :

  * ID
  * Description
  * Statut (Publique / Privée)
* Règles d’affichage :

  * non connecté : uniquement les arenas **publiques**,
  * membre connecté : arenas publiques + arenas qu’il possède,
  * admin : toutes les arenas.
* Actions :

  * **Voir** : accessibles à tous selon les règles ci-dessus
  * **Modifier** : visible seulement pour le créateur ou l’admin
  * **Créer une nouvelle arena** (si connecté)

#### Détails d’une arena — `/arena/{id}` (`app_arena_show`)

* Description
* Statut (publiée / privée)
* Propriétaire
* Liste des figures liées, cliquables
* Si créateur ou admin :

  * bouton **Modifier**
  * bouton de suppression (formulaire avec CSRF)

Les contrôles d’accès vérifient :

* si l’arena est publique,
* ou si le membre connecté en est le propriétaire,
* ou s’il a le rôle `ROLE_ADMIN`.

---

## 4. Comptes par défaut

Les fixtures créent au moins les comptes suivants :

### Administrateur

* **Email** : `admin@localhost`
* **Mot de passe** : `admin123`
* **Rôle** : `ROLE_ADMIN`

### Utilisateurs simples

* **Email** : `Olivier@localhost` – mot de passe `123456` – `ROLE_USER`
* **Email** : `Slash@localhost` – mot de passe `123456` – `ROLE_USER`

L’admin (`admin@localhost`) peut :

* voir / modifier toutes les vitrines,
* voir / modifier toutes les figures,
* voir / modifier toutes les arenas, y compris privées.

---
## 5. Structure des images

Les images utilisées dans l’application sont stockées dans :

* `public/uploads/figures/` → images des figures

Les fixtures peuvent référencer des fichiers d’images prédéfinis.
Il est possible de les remplacer par de vraies images en conservant les mêmes noms.
 
 ---

## Auteur
Projet pédagogique (CSC4101).  
Développé par Ismail Abid — Encadrant : Olivier Berger.
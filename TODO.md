# KaijuHunter — État d’Implémentation

## 1. Fonctionnalités Déjà Implémentées

### ✓ Modèle de données & Relations
- Entités principales :
  - `Member` (utilisateur)
  - `Vitrine` (inventaire privé du membre)
  - `Figure` (figurine associée à une vitrine, présente dans plusieurs arènes)
  - `Arena` (galerie publique/privée d’un membre)
- Relations :
  - `Member` 1–1 `Vitrine`
  - `Vitrine` 1–N `Figure`
  - `Member` 1–N `Arena`
  - `Arena` N–N `Figure`

### ✓ Authentification & Sécurité
- Entité `Member` générée avec `make:user` (email + password hashé).
- Provider configuré sur `Member` dans `security.yaml`.
- Login fonctionnel (form_login), redirection vers `/member/me`.
- Rôle par défaut : `ROLE_USER`.
- Accès global protégé par firewall `main`.
- Contextualisation de l’accès :
  - Chaque membre ne voit que ses propres données (vitrine, figures, arènes) via les contrôleurs.
  - Consultation publique des `Arena` selon le flag `publie` (public/privé).

### ✓ Gestion des Vitrines (Inventaires)
- Contrôleur `VitrineController` :
  - `vitrine_index` : liste de toutes les vitrines (vue “globale” encore accessible).
  - `vitrine_show` : consultation détaillée d’une vitrine et de ses figures.
- Lien depuis la fiche `Member` vers la vitrine du membre.
- Bouton “Voir” corrigé (route `vitrine_show` OK).
- Données générées via Fixtures (1 vitrine par membre, figures associées).

### ✓ Gestion des Figures (Objets)
- CRUD complet généré par `make:crud` (`FigureController`, `FigureType`, Twig).
- Contexte d’inventaire pris en compte pour la création (étape 14) :
  - Création de figure liée à une vitrine précise (depuis la vitrine).
  - Champ `vitrine` verrouillé (disabled) dans le formulaire.
- ManyToMany avec `Arena` fonctionnel.
- Formulaire `FigureType` :
  - Nom
  - Vitrine (non modifiable dans le formulaire — contexte)
  - Arènes associées (sélection multiple).
- Index des figures nettoyé (plus de bouton “Create new” global).

### ✓ Gestion des Arènes (Galeries)
- Entité `Arena` (description, `publie`, owner `Member`, figures N–N).
- CRUD complet via `ArenaController` + `ArenaType`.
- Contexte Membre pris en compte (étape 15) :
  - Index des arènes filtré ou lié à l’owner lorsqu’on passe par `Member`.
  - Redirections après création/édition/suppression renvoient vers la fiche `Member` correspondant.
- Page `arena/show` :
  - Affiche description, statut publié, owner, et liste des `Figure` associées.
  - Lien vers la vue contextuelle d’une figure dans une arène :
    - Route `app_arena_figure_show` avec `arena_id` et `figure_id`.
- Méthode `figureShow` dans `ArenaController` :
  - Vérifie que la figure appartient bien à l’arène.
  - Rend la vue `arena/figure_show.html.twig`.

### ✓ Gestion des Images (Étape 17)
- Champ `imageName` ajouté à l’entité `Figure`.
- Upload d’image depuis les formulaires de création/édition de `Figure` :
  - Champ `imageFile` de type `FileType` (non mappé).
  - Sauvegarde du fichier dans `public/uploads/figures`.
  - Nom de fichier unique stocké dans `Figure::imageName`.
- Affichage de l’image :
  - Dans `figure/show.html.twig` : carte avec aperçu de l’image si présente.
  - Dans `arena/figure_show.html.twig` : même logique, affichage de l’image dans le contexte de l’arène.
- Gestion basique d’erreur d’upload via flash message.

### ✓ Pages Membre & Navigation
- `MemberController` :
  - `index` : liste des membres (vue “globale” utilisée surtout pour les tests).
  - `show` : fiche membre :
    - Infos de base (ID, email).
    - Lien vers la vitrine du membre.
    - Liste des arènes de ce membre, avec liens vers chaque arène.
    - Bouton “Créer une arène” contextuel pour ce membre.
- Menu principal basé sur `render_bootstrap_menu('main')` + lien Arenas propre.
- Design global basé sur Start Bootstrap Shop Homepage, adapté à KaijuHunter :
  - Navbar cohérente.
  - Tables Bootstrap, cartes, badges (publish, etc.).
  - Boutons d’action clairs (Voir / Edit / Back).

### ✓ Fixtures (Données de Test)
- `AppFixtures` :
  - Création de plusieurs `Member` avec mot de passe hashé.
  - Une `Vitrine` par membre.
  - Plusieurs `Figure` par vitrine (thème Kaiju No. 8 + Solo Leveling).
  - Plusieurs `Arena` par membre, avec figures associées (ManyToMany).
- Commandes de reset + reload testées :
  - `symfony console doctrine:schema:drop --force`
  - `symfony console doctrine:schema:create`
  - `symfony console doctrine:fixtures:load --no-interaction`

---

## 2. Améliorations Optionnelles Non Encore Réalisées

### ☐ Design avancé des Arènes (Galeries publiques)
- Grille visuelle de figures avec cartes et images plus grandes.
- Mise en page type “gallery / board” (mosaïque).
- Affichage des images des figures directement dans `arena/index` et `arena/show` avec layout plus riche.

### ☐ Tableau de bord Membre
- Page de synthèse pour un membre connecté :
  - Nombre de figures, d’arènes, de vitrines.
  - Raccourcis rapides : “Créer une arène”, “Voir ma vitrine”, etc.
  - Statistiques simples (par ex. figures par arène).

### ☐ Recherche / Filtrage
- Recherche globale (par nom de figure, description d’arène, etc.).
- Filtres sur les arènes publiques (par owner, par nombre de figures, etc.).

### ☐ Gestion plus fine des droits
- Distinction claire front-office / back-office admin.
- Back-office avec CRUD complet sur tous les membres, vitrines, figures, arènes (ROLE_ADMIN).
- Accès aux arènes privées par le propriétaire + admin seulement.

### ☐ UX images
- Suppression automatique de l’ancienne image lors du remplacement.
- Image par défaut si aucune image n’est définie.
- Miniatures dans les listes (figure index, vitrine show, member show).

### ☐ Améliorations techniques
- Validation plus fine des formulaires (taille max image, types MIME).
- Tests unitaires/fonctionnels sur les contrôleurs (make:test).
- Nettoyage et homogénéisation complète des messages flash.

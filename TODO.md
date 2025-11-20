# KaijuHunter — État d’Implémentation

## 1. Fonctionnalités Déjà Implémentées

### ✓ Modèle & Relations
- `Member` (utilisateur)
- `Vitrine` (inventaire)
- `Figure` (objet d’un inventaire, peut être dans plusieurs arènes)
- `Arena` (galerie publique/privée)
- Relations : Member–Vitrine (1-1), Vitrine–Figure (1-N), Member–Arena (1-N), Arena–Figure (N-N)

### ✓ Authentification & Sécurité
- Login fonctionnel (email + mot de passe)
- ROLE_USER / ROLE_ADMIN opérationnels
- Accès restreint : chaque membre voit uniquement sa vitrine, ses figures et ses arènes
- Arènes publiques consultables par tout le monde
- Contrôleurs contextualisés (Figure/Arena/Vitrine)

### ✓ Vitrines
- CRUD opérationnel
- Consultation depuis le profil membre
- Lien “Voir ma vitrine” fonctionnel
- Redirections contextualisées
- Fixtures : une vitrine par membre

### ✓ Figures
- CRUD opérationnel
- Création contextualisée dans une vitrine (plus de création globale)
- `FigureType` : vitrine non modifiable, arènes multiples
- Route publique `app_arena_figure_show`
- Affichage visible depuis l’arène
- Upload d’images fonctionnel (imageName, imageFile, dossier uploads/figures)

### ✓ Arènes
- CRUD opérationnel
- Création contextualisée au membre
- Redirections cohérentes après edit/delete
- Affichage public/privé
- Figures listées avec lien vers la vue contextuelle
- Fixtures : plusieurs arènes par membre

### ✓ Pages Membre
- Liste et fiche membre
- Profil : vitrine + arènes du membre
- Boutons “Créer une arène” + navigation cohérente

### ✓ Fixtures
- Membres → vitrines → figures → arènes → liaisons ManyToMany
- Thème : Kaiju No.8 + Solo Leveling
- Réinitialisation testée (drop/create/load)

---

## 2. Améliorations Optionnelles Non Faites

### ☐ Design avancé des galeries  
- Grid visuelle, cartes avec images.
- Preview d’image en modal

### ☐ Tableau de bord membre  
- Statistiques + raccourcis.

### ☐ Recherche globale  
- Recherche figures/vitrines/arènes.

### ☐ UX images  
- Miniatures dans toutes les listes, suppression auto ancienne image.

### ☐ Back-office admin  
- CRUD complet accès total.


# 📜 Guide des Conventions

Ce document définit les standards de développement pour assurer la qualité, la traçabilité et la maintenabilité du code sur les dépôts `upply-backend` et `upply-frontend`.

## 🛡️ Protection de la branche principale (`main`)

La branche `main` est le reflet de la production (ou du code stable). Elle est protégée par les règles suivantes :

1.  **Push direct interdit** : Personne ne peut pusher directement sur `main`.
2.  **Pull Request (PR) obligatoire** : Toute modification doit passer par une branche intermédiaire et une PR.
3.  **Review obligatoire** : Au moins **1 approbation** d'un membre de l'équipe est requise pour valider le merge.
4.  **Résolution de conversations** : Tous les commentaires laissés lors de la review doivent être résolus avant le merge.
5.  **Historique linéaire** : Nous privilégions le *Squash and merge* ou le *Rebase* pour garder un historique propre.

## Convention de Nommage des Branches

Le nom des branches doit être explicite et suivre le format suivant : `type/description-succincte`

| Type | Usage | Exemple |
| :--- | :--- | :--- |
| `feature/` | Nouvelle fonctionnalité ou amélioration | `feature/auth-jwt-implementation` |
| `fix/` | Correction d'un bug | `fix/cors-issue-production` |
| `docs/` | Modification de la documentation | `docs/update-readme-install` |
| `refactor/` | Modification du code sans changement de logique | `refactor/clean-models-structure` |
| `test/` | Ajout ou modification de tests | `test/unit-tests-user-service` |

## Convention de Commits (Conventional Commits)

Nous utilisons le standard **Conventional Commits** pour faciliter la lecture de l'historique et l'automatisation du changelog.

### Format
`type(scope): description` *(le scope est optionnel mais recommandé)*

### Types autorisés
- **feat**: Une nouvelle fonctionnalité.
- **fix**: Une correction de bug.
- **docs**: Documentation uniquement.
- **style**: Formatage, points-virgules manquants, etc. (pas de changement de code).
- **refactor**: Modification du code qui ne corrige ni ne rajoute de fonction.
- **perf**: Amélioration de la performance.
- **chore**: Mise à jour de tâches de build, configuration d'outils, etc.

### Exemples
- `feat(api): add login endpoint with bcrypt`
- `fix(ui): responsive bug on mobile navbar`
- `docs: update conventions for commit messages`

## Flux de travail (Workflow)

1. Créer une branche locale : `git checkout -b feature/ma-feature`
2. Développer et commiter avec les messages normés.
3. Pusher la branche et ouvrir une Pull Request sur GitHub.
4. Demander une review à un collègue.
5. Une fois validé, merger via l'interface GitHub.

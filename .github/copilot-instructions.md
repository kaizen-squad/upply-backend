> [!IMPORTANT]
> Toutes les revues de code, explications et commentaires générés doivent être rédigés exclusivement en français, quel que soit le langage utilisé dans le code source.

# Upply — Copilot Instructions (Backend)

## Conventions obligatoires

Avant toute review, vérifie que les conventions définies dans [`REPOSITORY-CONVENTIONS.md`](../REPOSITORY-CONVENTIONS.md) sont respectées. Tout écart doit être signalé comme erreur bloquante.

---

## Règles de qualité — Non négociables

- **Gestion des erreurs :** Tout endpoint doit avoir une gestion d'erreur explicite. Aucun `500` non géré n'est acceptable. Si un bloc `try/catch` est absent sur une opération critique (appel API externe, opération base de données, changement de statut de transaction), signale-le comme erreur bloquante.
- **Valeurs sensibles :** Aucune clé API, mot de passe, token ou valeur sensible ne doit apparaître dans le code. Tout doit passer par les variables d'environnement (`.env`). Signale toute valeur hardcodée comme erreur bloquante.
- **Documentation API :** Tout endpoint créé ou modifié doit être accompagné d'une mise à jour de la collection Postman. Si la PR ajoute ou modifie un endpoint sans mention de mise à jour Postman, signale-le comme avertissement.

---

## Architecture backend — Règles strictes

Le backend suit une architecture modulaire en couches. Les violations de cette architecture sont des erreurs bloquantes.

```
app/
├── DTOs/          ← Transfert de données typées entre Controller et Service
├── Http/
│   ├── Controllers/Api/   ← Réception HTTP + délégation au Service uniquement
│   ├── Requests/          ← Validation des données entrantes
│   └── Resources/         ← Formatage des réponses JSON
├── Jobs/          ← ProcessPayout.php et autres jobs asynchrones
├── Mail/          ← Mailables Laravel
├── Models/        ← Eloquent uniquement, pas de logique métier
├── Services/      ← Toute la logique métier
└── Traits/
    └── Auth/HasAuthToken.php
```

**Règle absolue :** Les Controllers ne doivent contenir aucune logique métier. Si un Controller contient des requêtes Eloquent directes, des calculs ou des conditions métier, signale-le comme erreur bloquante.

---

## Stack technique de référence

| Couche           | Technologie                                                                                                                                                                                                                                                         |
| ---------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Frontend         | Next.js 14 + Tailwind CSS                                                                                                                                                                                                                                           |
| Backend          | Laravel 13 + PHP 8.3                                                                                                                                                                                                                                                |
| Base de données  | PostgreSQL                                                                                                                                                                                                                                                          |
| Authentification | Laravel Sanctum (gestion des tokens) + Refresh Token implémenté en interne — Sanctum ne fournit pas de refresh token nativement. Le refresh token est un second token Sanctum longue durée, stocké en cookie HttpOnly, géré manuellement via le Trait HasAuthToken. |
| Paiement         | FedaPay (sandbox)                                                                                                                                                                                                                                                   |
| Conteneurisation | Docker + Docker Compose                                                                                                                                                                                                                                             |
| Queue driver     | Redis                                                                                                                                                                                                                                                               |

---

## Modèle de données — Tables et colonnes de référence

### users

`id` (UUID) · `name` · `email` (unique) · `password` (hashed) · `role` enum(`client`, `prestataire`) · `phone` (nullable) · `rating_avg` decimal(3,2) · timestamps

### tasks

`id` (UUID) · `client_id` (FK) · `prestataire_id` (FK, nullable) · `title` · `description` · `budget` integer · `deadline` date · `status` enum(`OUVERTE`, `EN_COURS`, `LIVREE`, `VALIDEE`) · timestamps

### applications

`id` (UUID) · `task_id` (FK) · `prestataire_id` (FK) · `message` · `status` enum(`EN_ATTENTE`, `ACCEPTEE`, `REJETEE`) · timestamps

### contracts (décision d'architecture)
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> f8e09d0 (docs: documenter la décision d'architecture contracts)
=======
>>>>>>> f8e09d0 (docs: documenter la décision d'architecture contracts)
=======
>>>>>>> f8e09d0 (docs: documenter la décision d'architecture contracts)

`id` (UUID) · `application_id` (FK, unique) · timestamps

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 3201d19 (docs: expliciter l'unicité métier des contrats)
=======
>>>>>>> dfba593 (docs: expliciter hasOneThrough et DB::transaction pour contracts)
=======
>>>>>>> 339d3fb (docs: expliciter l'unicité métier des contrats)
=======
>>>>>>> 3793991 (docs: expliciter hasOneThrough et DB::transaction pour contracts)
=======
>>>>>>> 343be85 (docs: expliciter l'unicité métier des contrats)
=======
>>>>>>> a129b2e (docs: expliciter hasOneThrough et DB::transaction pour contracts)
Décision retenue pour cette architecture : la table `contracts` matérialise l'acceptation d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. L'unicité de `application_id` garantit qu'une candidature ne peut générer qu'un seul contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`) à définir sur `Task` (`public function contract(): HasOneThrough`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
=======
<<<<<<< HEAD
`id` (UUID) · `application_id` (FK, unique) · timestamps

Décision retenue pour cette architecture : la table `contracts` matérialise la retenue d'une `application` et formalise le lien contractuel. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> dac7574 (docs: lever les ambiguïtés sur la décision contracts)
=======
Décision retenue pour cette architecture : la table `contracts` matérialise l'acceptation d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. L'unicité de `application_id` garantit qu'une candidature ne peut générer qu'un seul contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 50412c1 (docs: expliciter l'unicité métier des contrats)
=======
Décision retenue pour cette architecture : la table `contracts` matérialise l'acceptation d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. L'unicité de `application_id` garantit qu'une candidature ne peut générer qu'un seul contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`) à définir sur `Task` (`public function contract(): HasOneThrough`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 722f152 (docs: expliciter hasOneThrough et DB::transaction pour contracts)
=======
Une table `contracts` peut être introduite en optimisation pour matérialiser la retenue d'une `application` et formaliser le lien contractuel. Dans ce cas, le contrat est lié à `applications` et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`).
>>>>>>> 1dc7611 (docs: documenter la décision d'architecture contracts)
>>>>>>> a498129 (docs: documenter la décision d'architecture contracts)
=======
`id` (UUID) · `application_id` (FK, unique recommandé) · timestamps

Une table `contracts` peut être introduite en optimisation pour matérialiser la retenue d'une `application` et formaliser le lien contractuel. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`).
>>>>>>> 1605468 (docs: préciser le schéma contracts et son impact sur la machine d'états)
=======
`id` (UUID) · `application_id` (FK, unique) · timestamps

<<<<<<< HEAD
Décision retenue pour cette architecture : la table `contracts` matérialise la retenue d'une `application` et formalise le lien contractuel. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 0871580 (docs: lever les ambiguïtés sur la décision contracts)
=======
Décision retenue pour cette architecture : la table `contracts` matérialise la retenue d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 865247d (docs: préciser la condition ACCEPTEE et l'atomicité de création de contrat)
=======
Décision retenue pour cette architecture : la table `contracts` matérialise l'acceptation d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. L'unicité de `application_id` garantit qu'une candidature ne peut générer qu'un seul contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 01289f6 (docs: expliciter l'unicité métier des contrats)
=======
Décision retenue pour cette architecture : la table `contracts` matérialise l'acceptation d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. L'unicité de `application_id` garantit qu'une candidature ne peut générer qu'un seul contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`) à définir sur `Task` (`public function contract(): HasOneThrough`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 252c584 (docs: expliciter hasOneThrough et DB::transaction pour contracts)
=======
Une table `contracts` peut être introduite en optimisation pour matérialiser la retenue d'une `application` et formaliser le lien contractuel. Dans ce cas, le contrat est lié à `applications` et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`).
>>>>>>> 1dc7611 (docs: documenter la décision d'architecture contracts)
=======
`id` (UUID) · `application_id` (FK, unique recommandé) · timestamps

Une table `contracts` peut être introduite en optimisation pour matérialiser la retenue d'une `application` et formaliser le lien contractuel. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`).
>>>>>>> 1605468 (docs: préciser le schéma contracts et son impact sur la machine d'états)
=======
`id` (UUID) · `application_id` (FK, unique) · timestamps

<<<<<<< HEAD
Décision retenue pour cette architecture : la table `contracts` matérialise la retenue d'une `application` et formalise le lien contractuel. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 0871580 (docs: lever les ambiguïtés sur la décision contracts)
=======
Décision retenue pour cette architecture : la table `contracts` matérialise la retenue d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 865247d (docs: préciser la condition ACCEPTEE et l'atomicité de création de contrat)
=======
Décision retenue pour cette architecture : la table `contracts` matérialise l'acceptation d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. L'unicité de `application_id` garantit qu'une candidature ne peut générer qu'un seul contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 01289f6 (docs: expliciter l'unicité métier des contrats)
=======
Décision retenue pour cette architecture : la table `contracts` matérialise l'acceptation d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. L'unicité de `application_id` garantit qu'une candidature ne peut générer qu'un seul contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`) à définir sur `Task` (`public function contract(): HasOneThrough`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 252c584 (docs: expliciter hasOneThrough et DB::transaction pour contracts)
=======
Une table `contracts` peut être introduite en optimisation pour matérialiser la retenue d'une `application` et formaliser le lien contractuel. Dans ce cas, le contrat est lié à `applications` et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`).
>>>>>>> 1dc7611 (docs: documenter la décision d'architecture contracts)
=======
`id` (UUID) · `application_id` (FK, unique recommandé) · timestamps

Une table `contracts` peut être introduite en optimisation pour matérialiser la retenue d'une `application` et formaliser le lien contractuel. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`).
>>>>>>> 1605468 (docs: préciser le schéma contracts et son impact sur la machine d'états)
=======
`id` (UUID) · `application_id` (FK, unique) · timestamps

<<<<<<< HEAD
Décision retenue pour cette architecture : la table `contracts` matérialise la retenue d'une `application` et formalise le lien contractuel. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 0871580 (docs: lever les ambiguïtés sur la décision contracts)
=======
Décision retenue pour cette architecture : la table `contracts` matérialise la retenue d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 865247d (docs: préciser la condition ACCEPTEE et l'atomicité de création de contrat)
=======
Décision retenue pour cette architecture : la table `contracts` matérialise l'acceptation d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. L'unicité de `application_id` garantit qu'une candidature ne peut générer qu'un seul contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 01289f6 (docs: expliciter l'unicité métier des contrats)
=======
Décision retenue pour cette architecture : la table `contracts` matérialise l'acceptation d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. L'unicité de `application_id` garantit qu'une candidature ne peut générer qu'un seul contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`) à définir sur `Task` (`public function contract(): HasOneThrough`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.
>>>>>>> 252c584 (docs: expliciter hasOneThrough et DB::transaction pour contracts)

### deliverables

`id` (UUID) · `task_id` (FK) · `prestataire_id` (FK) · `content` text · `file_path` (nullable) · `submitted_at`

### transactions

`id` (UUID) · `task_id` (FK) · `client_id` (FK) · `prestataire_id` (FK) · `fedapay_transaction_id` · `amount_gross` integer · `commission` integer · `amount_net` integer · `status` enum(`EN_SEQUESTRE`, `EN_LIBERATION`, `LIBERE`) · `liberated_at` (nullable pour `EN_SEQUESTRE` et `EN_LIBERATION`, obligatoirement renseigné au passage à `LIBERE`) · timestamps

### transaction_logs

`id` (UUID) · `transaction_id` (FK) · `from_status` · `to_status` · `triggered_by` (FK users) · `note` (nullable) · `created_at`

### reviews
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD

<<<<<<< HEAD
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 04e956f (docs(schema): aligner reviews avec soft delete pour audit)
=======
>>>>>>> c4ff55a (docs(schema): aligner reviews avec soft delete pour audit)
>>>>>>> 9f464ed (docs(schema): aligner reviews avec soft delete pour audit)
=======
>>>>>>> 3c4381a (feat(tasks): Defined task storage DTO)
=======
=======
>>>>>>> 1a2c557 (docs(schema): aligner reviews avec soft delete pour audit)
<<<<<<< HEAD
>>>>>>> 7cabcd1 (docs(schema): aligner reviews avec soft delete pour audit)
=======
=======

>>>>>>> f8e09d0 (docs: documenter la décision d'architecture contracts)
<<<<<<< HEAD
>>>>>>> e886ac5 (docs: documenter la décision d'architecture contracts)
=======
=======

=======
>>>>>>> c4ff55a (docs(schema): aligner reviews avec soft delete pour audit)
>>>>>>> 830d6f1 (docs(schema): aligner reviews avec soft delete pour audit)
<<<<<<< HEAD
>>>>>>> 882b4c3 (docs(schema): aligner reviews avec soft delete pour audit)
=======
=======

>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
<<<<<<< HEAD
>>>>>>> e9cdbef (feat(tasks): Defined task storage DTO)
=======
=======
>>>>>>> 1a2c557 (docs(schema): aligner reviews avec soft delete pour audit)
<<<<<<< HEAD
>>>>>>> 75ee0d8 (docs(schema): aligner reviews avec soft delete pour audit)
=======
=======

>>>>>>> f8e09d0 (docs: documenter la décision d'architecture contracts)
<<<<<<< HEAD
>>>>>>> 8168db4 (docs: documenter la décision d'architecture contracts)
=======
=======

=======
>>>>>>> c4ff55a (docs(schema): aligner reviews avec soft delete pour audit)
>>>>>>> 830d6f1 (docs(schema): aligner reviews avec soft delete pour audit)
<<<<<<< HEAD
>>>>>>> 36c32b8 (docs(schema): aligner reviews avec soft delete pour audit)
=======
=======

>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
<<<<<<< HEAD
>>>>>>> a1a3766 (feat(tasks): Defined task storage DTO)
=======
=======
>>>>>>> 1a2c557 (docs(schema): aligner reviews avec soft delete pour audit)
<<<<<<< HEAD
>>>>>>> 1dc581c (docs(schema): aligner reviews avec soft delete pour audit)
=======
=======

>>>>>>> f8e09d0 (docs: documenter la décision d'architecture contracts)
<<<<<<< HEAD
>>>>>>> 3f09052 (docs: documenter la décision d'architecture contracts)
=======
=======

=======
>>>>>>> c4ff55a (docs(schema): aligner reviews avec soft delete pour audit)
>>>>>>> 830d6f1 (docs(schema): aligner reviews avec soft delete pour audit)
<<<<<<< HEAD
>>>>>>> 75f4876 (docs(schema): aligner reviews avec soft delete pour audit)
=======
=======

>>>>>>> 470409b (feat(tasks): Defined task storage DTO)
>>>>>>> 6327f2e (feat(tasks): Defined task storage DTO)
`id` (UUID) · `task_id` (FK) · `reviewer_id` (FK) · `reviewee_id` (FK) · `rating` smallint avec contrainte CHECK (rating >= 1 AND rating <= 5) · `comment` (nullable) · `created_at` · `updated_at` · `deleted_at` (soft delete, conservation pour audit)

---

## Machines à états — Transitions valides uniquement

### Tâche

```
OUVERTE → EN_COURS (client sélectionne + paiement confirmé en escrow)
EN_COURS → LIVREE (prestataire soumet un livrable)
LIVREE → VALIDEE (client valide le livrable)
```

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 9bd7881 (docs: lever les ambiguïtés sur la décision contracts)
=======
>>>>>>> 823945a (docs: préciser la condition ACCEPTEE et l'atomicité de création de contrat)
=======
>>>>>>> 4133427 (docs: reformuler la règle d'atomicité de création de contrat)
=======
>>>>>>> 3201d19 (docs: expliciter l'unicité métier des contrats)
=======
>>>>>>> dfba593 (docs: expliciter hasOneThrough et DB::transaction pour contracts)
=======
>>>>>>> 91d8110 (docs: préciser le schéma contracts et son impact sur la machine d'états)
=======
>>>>>>> 3d74550 (docs: lever les ambiguïtés sur la décision contracts)
=======
>>>>>>> de7000a (docs: préciser la condition ACCEPTEE et l'atomicité de création de contrat)
=======
>>>>>>> e412133 (docs: reformuler la règle d'atomicité de création de contrat)
=======
>>>>>>> 339d3fb (docs: expliciter l'unicité métier des contrats)
=======
>>>>>>> 3793991 (docs: expliciter hasOneThrough et DB::transaction pour contracts)
=======
>>>>>>> b9d4d0a (docs: préciser le schéma contracts et son impact sur la machine d'états)
=======
>>>>>>> 24d211d (docs: lever les ambiguïtés sur la décision contracts)
=======
>>>>>>> c28f0bf (docs: préciser la condition ACCEPTEE et l'atomicité de création de contrat)
=======
>>>>>>> ee60466 (docs: reformuler la règle d'atomicité de création de contrat)
=======
>>>>>>> 343be85 (docs: expliciter l'unicité métier des contrats)
=======
>>>>>>> a129b2e (docs: expliciter hasOneThrough et DB::transaction pour contracts)
Dans cette architecture, la mise à jour de l'application vers `ACCEPTEE`, la transition `OUVERTE → EN_COURS`, la création du contrat et la création de la transaction financière d'escrow doivent s'effectuer dans un `DB::transaction()` unique (tout ou rien). En cas d'échec, le rollback doit être complet, sans introduction d'un nouvel état métier.
=======
Si l'architecture `contracts` est activée, la création du contrat est concomitante à la transition `OUVERTE → EN_COURS` et ne crée pas de nouvel état métier.
>>>>>>> 44dc7ba (docs: préciser le schéma contracts et son impact sur la machine d'états)
=======
Dans cette architecture, la création du contrat est concomitante à la transition `OUVERTE → EN_COURS` et ne crée pas de nouvel état métier.
>>>>>>> dac7574 (docs: lever les ambiguïtés sur la décision contracts)
=======
Dans cette architecture, la transition `OUVERTE → EN_COURS` et la création du contrat doivent s'effectuer au sein d'une même transaction atomique (tout ou rien), sans introduction d'un nouvel état métier.
>>>>>>> afc7ecd (docs: reformuler la règle d'atomicité de création de contrat)
=======
Dans cette architecture, la mise à jour de l'application vers `ACCEPTEE`, la transition `OUVERTE → EN_COURS` et la création du contrat doivent s'effectuer au sein d'une même transaction atomique (tout ou rien), sans introduction d'un nouvel état métier.
>>>>>>> 50412c1 (docs: expliciter l'unicité métier des contrats)
=======
<<<<<<< HEAD
Dans cette architecture, la mise à jour de l'application vers `ACCEPTEE`, la transition `OUVERTE → EN_COURS`, la création du contrat et la création de la transaction financière d'escrow doivent s'effectuer dans un `DB::transaction()` unique (tout ou rien). En cas d'échec, le rollback doit être complet, sans introduction d'un nouvel état métier.
>>>>>>> 722f152 (docs: expliciter hasOneThrough et DB::transaction pour contracts)
=======
Si l'architecture `contracts` est activée, la création du contrat est concomitante à la transition `OUVERTE → EN_COURS` et ne crée pas de nouvel état métier.
>>>>>>> 1605468 (docs: préciser le schéma contracts et son impact sur la machine d'états)
=======
Dans cette architecture, la création du contrat est concomitante à la transition `OUVERTE → EN_COURS` et ne crée pas de nouvel état métier.
>>>>>>> 0871580 (docs: lever les ambiguïtés sur la décision contracts)
=======
Dans cette architecture, la transition `OUVERTE → EN_COURS` et la création du contrat doivent être traitées dans une même transaction atomique (tout ou rien), sans créer de nouvel état métier.
>>>>>>> 865247d (docs: préciser la condition ACCEPTEE et l'atomicité de création de contrat)
=======
Dans cette architecture, la transition `OUVERTE → EN_COURS` et la création du contrat doivent s'effectuer au sein d'une même transaction atomique (tout ou rien), sans introduction d'un nouvel état métier.
>>>>>>> e432a73 (docs: reformuler la règle d'atomicité de création de contrat)
=======
Dans cette architecture, la mise à jour de l'application vers `ACCEPTEE`, la transition `OUVERTE → EN_COURS` et la création du contrat doivent s'effectuer au sein d'une même transaction atomique (tout ou rien), sans introduction d'un nouvel état métier.
>>>>>>> 01289f6 (docs: expliciter l'unicité métier des contrats)
=======
Dans cette architecture, la mise à jour de l'application vers `ACCEPTEE`, la transition `OUVERTE → EN_COURS`, la création du contrat et la création de la transaction financière d'escrow doivent s'effectuer dans un `DB::transaction()` unique (tout ou rien). En cas d'échec, le rollback doit être complet, sans introduction d'un nouvel état métier.
>>>>>>> 252c584 (docs: expliciter hasOneThrough et DB::transaction pour contracts)
=======
Si l'architecture `contracts` est activée, la création du contrat est concomitante à la transition `OUVERTE → EN_COURS` et ne crée pas de nouvel état métier.
>>>>>>> 1605468 (docs: préciser le schéma contracts et son impact sur la machine d'états)
=======
Dans cette architecture, la création du contrat est concomitante à la transition `OUVERTE → EN_COURS` et ne crée pas de nouvel état métier.
>>>>>>> 0871580 (docs: lever les ambiguïtés sur la décision contracts)
=======
Dans cette architecture, la transition `OUVERTE → EN_COURS` et la création du contrat doivent être traitées dans une même transaction atomique (tout ou rien), sans créer de nouvel état métier.
>>>>>>> 865247d (docs: préciser la condition ACCEPTEE et l'atomicité de création de contrat)
=======
Dans cette architecture, la transition `OUVERTE → EN_COURS` et la création du contrat doivent s'effectuer au sein d'une même transaction atomique (tout ou rien), sans introduction d'un nouvel état métier.
>>>>>>> e432a73 (docs: reformuler la règle d'atomicité de création de contrat)
=======
Dans cette architecture, la mise à jour de l'application vers `ACCEPTEE`, la transition `OUVERTE → EN_COURS` et la création du contrat doivent s'effectuer au sein d'une même transaction atomique (tout ou rien), sans introduction d'un nouvel état métier.
>>>>>>> 01289f6 (docs: expliciter l'unicité métier des contrats)
=======
Dans cette architecture, la mise à jour de l'application vers `ACCEPTEE`, la transition `OUVERTE → EN_COURS`, la création du contrat et la création de la transaction financière d'escrow doivent s'effectuer dans un `DB::transaction()` unique (tout ou rien). En cas d'échec, le rollback doit être complet, sans introduction d'un nouvel état métier.
>>>>>>> 252c584 (docs: expliciter hasOneThrough et DB::transaction pour contracts)
=======
Si l'architecture `contracts` est activée, la création du contrat est concomitante à la transition `OUVERTE → EN_COURS` et ne crée pas de nouvel état métier.
>>>>>>> 1605468 (docs: préciser le schéma contracts et son impact sur la machine d'états)
=======
Dans cette architecture, la création du contrat est concomitante à la transition `OUVERTE → EN_COURS` et ne crée pas de nouvel état métier.
>>>>>>> 0871580 (docs: lever les ambiguïtés sur la décision contracts)
=======
Dans cette architecture, la transition `OUVERTE → EN_COURS` et la création du contrat doivent être traitées dans une même transaction atomique (tout ou rien), sans créer de nouvel état métier.
>>>>>>> 865247d (docs: préciser la condition ACCEPTEE et l'atomicité de création de contrat)
=======
Dans cette architecture, la transition `OUVERTE → EN_COURS` et la création du contrat doivent s'effectuer au sein d'une même transaction atomique (tout ou rien), sans introduction d'un nouvel état métier.
>>>>>>> e432a73 (docs: reformuler la règle d'atomicité de création de contrat)
=======
Dans cette architecture, la mise à jour de l'application vers `ACCEPTEE`, la transition `OUVERTE → EN_COURS` et la création du contrat doivent s'effectuer au sein d'une même transaction atomique (tout ou rien), sans introduction d'un nouvel état métier.
>>>>>>> 01289f6 (docs: expliciter l'unicité métier des contrats)
=======
Dans cette architecture, la mise à jour de l'application vers `ACCEPTEE`, la transition `OUVERTE → EN_COURS`, la création du contrat et la création de la transaction financière d'escrow doivent s'effectuer dans un `DB::transaction()` unique (tout ou rien). En cas d'échec, le rollback doit être complet, sans introduction d'un nouvel état métier.
>>>>>>> 252c584 (docs: expliciter hasOneThrough et DB::transaction pour contracts)

Toute autre transition est invalide. Signale comme erreur bloquante toute implémentation qui permet une transition non listée ci-dessus.

### Transaction

```
EN_SEQUESTRE → EN_LIBERATION (client valide le livrable, début du payout avant confirmation finale)
EN_LIBERATION → LIBERE (payout confirmé)
EN_LIBERATION → EN_SEQUESTRE (échec payout confirmé par réconciliation)
```

Le statut d'une transaction ne doit jamais changer sans vérification préalable côté serveur des conditions requises. Signale comme erreur bloquante tout changement de statut sans validation des conditions.
Le champ `liberated_at` doit être renseigné uniquement au passage vers `LIBERE` (jamais lors du passage en `EN_LIBERATION`).
Le job `ProcessPayoutReconciliation` doit traiter tout statut `EN_LIBERATION` bloqué : vérifier les transactions restées `EN_LIBERATION` au-delà de `FEDAPAY_RECONCILIATION_TIMEOUT_MINUTES` minutes (valeur définie dans `.env`, mappée dans `config/fedapay.php`), contrôler l'état réel côté FedaPay via `fedapay_transaction_id`, puis soit confirmer le payout (transition vers `LIBERE`), soit marquer l'échec et revenir à `EN_SEQUESTRE`.

---

## Endpoints de référence

### Auth

`POST /api/auth/register` · `POST /api/auth/login` · `POST /api/auth/refresh` (cookie) · `POST /api/auth/logout` (Bearer)

### Tâches

`GET /api/tasks` · `GET /api/tasks/{id}` · `POST /api/tasks` (client) · `GET /api/tasks/mine` (client) · `DELETE /api/tasks/{id}` (client, OUVERTE uniquement)

### Candidatures

`POST /api/tasks/{id}/apply` (prestataire) · `GET /api/tasks/{id}/applications` (client) · `GET /api/applications/mine` (prestataire) · `PATCH /api/applications/{id}/accept` (client) · `PATCH /api/applications/{id}/reject` (client)

### Paiement & Escrow

`POST /api/tasks/{id}/payment/verify` (client) · `GET /api/tasks/{id}/transaction` (les deux)

### Livrables

`POST /api/tasks/{id}/deliver` (prestataire) · `GET /api/tasks/{id}/deliverable` (client) · `POST /api/tasks/{id}/validate` (client → déclenche payout)

### Notation & Dashboard

`POST /api/tasks/{id}/review` (les deux) · `GET /api/dashboard/client` · `GET /api/dashboard/prestataire`

---

## Règles de sécurité spécifiques aux transactions

- Tout appel à l'API FedaPay doit être dans un bloc `try/catch` avec logging de l'erreur
- Tout changement de statut de transaction doit être enregistré dans `transaction_logs` avec `triggered_by` et horodatage
- Le `amount_net` doit toujours être calculé comme `amount_gross - commission` (10 %) avant tout payout
- Le numéro Mobile Money du prestataire (`phone`) doit être validé comme non-null avant de déclencher le Job `ProcessPayout`
- Le job `ProcessPayoutReconciliation` doit vérifier l'état réel FedaPay via `fedapay_transaction_id` avant toute finalisation (`LIBERE`) ou rollback (`EN_SEQUESTRE`) d'une transaction bloquée en `EN_LIBERATION`

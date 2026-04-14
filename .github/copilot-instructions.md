>[!IMPORTANT]
>Toutes les revues de code, explications et commentaires générés doivent être rédigés exclusivement en français, quel que soit le langage utilisé dans le code source.

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

| Couche | Technologie |
|---|---|
| Frontend | Next.js 14 + Tailwind CSS |
| Backend | Laravel 13 + PHP 8.3  |
| Base de données | PostgreSQL |
| Authentification | Laravel Sanctum (gestion des tokens) + Refresh Token implémenté en interne — Sanctum ne fournit pas de refresh token nativement. Le refresh token est un second token Sanctum longue durée, stocké en cookie HttpOnly, géré manuellement via le Trait HasAuthToken. |
| Paiement | FedaPay (sandbox) |
| Conteneurisation | Docker + Docker Compose |
| Queue driver | Redis |

---

## Modèle de données — Tables et colonnes de référence

### users
`id` (UUID) · `name` · `email` (unique) · `password` (hashed) · `role` enum(`client`, `prestataire`) · `phone` (nullable) · `rating_avg` decimal(3,2) · timestamps

### tasks
`id` (UUID) · `client_id` (FK) · `prestataire_id` (FK, nullable) · `title` · `description` · `budget` integer · `deadline` date · `status` enum(`OUVERTE`, `EN_COURS`, `LIVREE`, `VALIDEE`) · timestamps

### applications
`id` (UUID) · `task_id` (FK) · `prestataire_id` (FK) · `message` · `status` enum(`EN_ATTENTE`, `ACCEPTEE`, `REJETEE`) · timestamps

### contracts (décision d'architecture)
`id` (UUID) · `application_id` (FK, unique) · timestamps

Décision retenue pour cette architecture : la table `contracts` matérialise la retenue d'une `application` et formalise le lien contractuel. Seule une `application` au statut `ACCEPTEE` peut créer un contrat. Le contrat est lié à `applications`, et l'accès depuis `tasks` se fait par transitivité (`hasOneThrough` Laravel via `Application`). Le schéma minimal attendu à ce stade est volontairement limité aux colonnes ci-dessus.

### deliverables
`id` (UUID) · `task_id` (FK) · `prestataire_id` (FK) · `content` text · `file_path` (nullable) · `submitted_at`

### transactions
`id` (UUID) · `task_id` (FK) · `client_id` (FK) · `prestataire_id` (FK) · `fedapay_transaction_id` · `amount_gross` integer · `commission` integer · `amount_net` integer · `status` enum(`EN_SEQUESTRE`, `LIBERE`) · `liberated_at` (nullable) · timestamps

### transaction_logs
`id` (UUID) · `transaction_id` (FK) · `from_status` · `to_status` · `triggered_by` (FK users) · `note` (nullable) · `created_at`

### reviews
`id` (UUID) · `task_id` (FK) · `reviewer_id` (FK) · `reviewee_id` (FK) · `rating` smallint avec contrainte CHECK (rating >= 1 AND rating <= 5) · `comment` (nullable) · `created_at`

---

## Machines à états — Transitions valides uniquement

### Tâche

```
OUVERTE → EN_COURS (client sélectionne + paiement confirmé en escrow)
EN_COURS → LIVREE (prestataire soumet un livrable)
LIVREE → VALIDEE (client valide le livrable)
```

Dans cette architecture, la transition `OUVERTE → EN_COURS` et la création du contrat doivent être traitées dans une même transaction atomique (tout ou rien), sans créer de nouvel état métier.

Toute autre transition est invalide. Signale comme erreur bloquante toute implémentation qui permet une transition non listée ci-dessus.

### Transaction

```
EN_SEQUESTRE → LIBERE (client valide le livrable → déclenche Job ProcessPayout)
```

Le statut d'une transaction ne doit jamais changer sans vérification préalable côté serveur des conditions requises. Signale comme erreur bloquante tout changement de statut sans validation des conditions.

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

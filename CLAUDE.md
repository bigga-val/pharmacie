# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Afya est une application web de gestion d'entreprise full-stack construite avec **Symfony 6.1** et **Doctrine ORM**. Elle gère l'inventaire, les ventes/factures, les employés, la paie, et le suivi financier (entrées/sorties de caisse). La base de données s'appelle `db_pharmacie`, ce qui indique un usage orienté pharmacie/distribution.

## Common Commands

### PHP / Symfony
```bash
composer install                          # Install PHP dependencies
php bin/console server:run                # Start development server
php bin/console doctrine:migrations:migrate  # Run database migrations
php bin/console doctrine:migrations:diff     # Generate migration from entity changes
php bin/console cache:clear               # Clear Symfony cache
php bin/console make:entity               # Scaffold new entity
php bin/console make:controller           # Scaffold new controller
php bin/console make:form                 # Scaffold new form type
php bin/console make:migration            # Generate migration file
```

### Frontend (Tailwind CSS)
```bash
npm install          # Install Node dependencies
npm run dev          # Watch and recompile Tailwind CSS
npm run build        # Build Tailwind CSS for production (minified)
```

### Testing
```bash
php bin/phpunit                           # Run all tests
php bin/phpunit tests/path/to/TestFile.php  # Run a single test file
php bin/phpunit --filter testMethodName   # Run a single test by name
```

## Architecture

### Request Lifecycle
```
HTTP Request → public/index.php → Symfony Kernel → Router →
  Controller → (Repository query / Form handling) →
  EntityManager::flush() → Twig render / JsonResponse / RedirectResponse
```

### Source Structure (`src/`)
- **Controller/** — un par domaine. Routes définies via attributs `#[Route]`. Retournent des templates Twig, des `JsonResponse` ou des `RedirectResponse`.
- **Entity/** — entités Doctrine ORM. Tout changement d'entité nécessite de générer et d'exécuter une migration.
- **Repository/** — un par entité. Les requêtes personnalisées vont ici, pas dans les contrôleurs.
- **Form/** — classes Symfony FormType liées aux entités.
- **Service/** — `PdfService` (Dompdf) et `FPdfGenerator` (FPDF) pour la génération de PDF (factures, fiches de paie, reçus).
- **Security/** — `LoginFormAuthenticator` (login par formulaire).

### Entités du domaine (13 entités)

| Entité | Description |
|--------|-------------|
| `Produits` | Catalogue produits (prix achat/vente, stock min/max, date péremption, image) |
| `CategorieProduit` | Catégories avec pourcentage de marge |
| `Approvisionnement` | Mouvements de stock (entrées et ajustements) |
| `Vente` | Factures/ventes (statut : progress / paid / canceled) |
| `ProduitVendu` | Lignes de vente (produit, qté, prix unitaire, taux) |
| `Employe` | Dossiers employés (salaire journalier, date embauche) |
| `Paie` | Périodes de paie (libellé, mois, année) |
| `PaieEmploye` | Détail paie par employé (jours, primes, déductions, total) |
| `Credit` | Sorties de caisse (dépenses) |
| `Debit` | Entrées de caisse (recettes hors ventes) |
| `Taux` | Taux de change (un seul actif à la fois) |
| `User` | Comptes utilisateurs (auth, rôles, profil) |
| `AuditLog` | Journal d'audit (action, entité, ancienne/nouvelle valeur, IP) |

### Templates (`templates/`)
Organisés par domaine, en miroir de la structure des contrôleurs. Les composants partagés sont dans `templates/components/layout/`. Les templates Twig utilisent les classes utilitaires Tailwind CSS.

### Frontend
Tailwind CSS 3.2 avec une palette personnalisée (`primary`, `secondary`, `success`, `danger`, `warning`, `info`, `dark`) et la police Nunito. Bootstrap et Toastr (notifications toast) sont servis depuis `public/assets/`. Le `tailwind.config.js` scanne `./templates/**/*.twig` pour les noms de classes.

### Authentification
Login par formulaire via `LoginFormAuthenticator`. Après connexion, redirection vers `app_set_sessions` (route de configuration de session).

- **Contrôle d'accès :** `ROLE_ADMIN` requis pour le dashboard, l'ajustement de stock, la gestion des utilisateurs et la caisse. Les utilisateurs standards ne peuvent modifier que leur propre compte.
- **Mot de passe par défaut** pour les nouveaux utilisateurs : `Afya@{année}` (ex: `Afya@2025`), affiché dans le flash message à la création. L'utilisateur doit le changer via `/reset_password`.

### Base de données
MySQL/MariaDB, base nommée `db_pharmacie`. Connexion configurée dans `.env` (`DATABASE_URL`). Doctrine gère le schéma via les migrations dans `migrations/`.

---

## Gestion du stock

### Calcul du stock disponible
Le stock n'est **jamais stocké en dur** sur l'entité `Produits`. Il est toujours calculé dynamiquement par `ApprovisionnementRepository::stockProduitByDate()` :

```
Stock disponible =
    SUM(Approvisionnement.qty)
    - SUM(ProduitVendu.qty WHERE Vente.statusVente = 'paid')
    - SUM(ProduitVendu.qty WHERE Vente.statusVente = 'progress')
```

La méthode retourne aussi un flag `stockBas` (1 si `stockDisponible <= minimum`, sinon 0) et trie les résultats par `stockBas DESC` (produits critiques en premier).

### Mouvements de stock
| Source | Impact | Champ clé |
|---|---|---|
| `Approvisionnement` (type=`approvisionnement`) | Entrée | `qty` positif |
| `Approvisionnement` (type=`ajustement`) | Correction | `qty` positif ou négatif |
| `ProduitVendu` (vente `paid`) | Sortie définitive | `qty` |
| `ProduitVendu` (vente `progress`) | Réservation | `qty` |

### Ajustement de stock
Route : `/approvisionnement/ajustement` (admin uniquement)
Formulaire : `AjustementStockType` (produit, quantité, motif)

- Une quantité **négative** réduit le stock (perte, vol, produit avarié, etc.)
- Une quantité **positive** augmente le stock (correction à la hausse)
- Le champ `cout` est forcé à `0` (pas d'impact financier)
- Le champ `type` est forcé à `'ajustement'` pour distinguer des approvisionnements réels
- Les ajustements apparaissent dans la liste `/approvisionnement/` avec un badge "Ajustement"

Motifs disponibles : Correction inventaire, Perte, Vol, Produit avarié, Retour client, Autre.

### Seuils stock et péremptions
- `Produits.minimum` (float nullable) — seuil de stock minimum
- `Produits.maximum` (float nullable) — seuil de stock maximum
- `Produits.preemption` (DateTimeInterface nullable) — date de péremption du produit
- La page `/approvisionnement/stock` colore déjà les lignes : orange si `stockDisponible <= minimum * 1.1`, rouge si `stockDisponible <= minimum`

### Entité `Approvisionnement` — champs notables
| Champ | Type | Description |
|---|---|---|
| `qty` | float | Quantité (peut être négative) |
| `cout` | float | Coût total (qty × prixUnitaire), 0 pour les ajustements |
| `taux` | float | Taux de change actif au moment de l'opération |
| `type` | string | `approvisionnement` ou `ajustement` |
| `motif` | string | Motif de l'ajustement (nullable) |

---

## Ventes et facturation

- Création d'une vente : formulaire principal + ajout de lignes via endpoints JSON (`jsonSaveVente`, `jsonSaveLigneVente`)
- Numérotation automatique des factures : format `V00001`
- Statuts : `progress` (en cours) → `paid` (confirmée) → `canceled` (annulée)
- Génération PDF via `FPdfGenerator::generateInvoicePdf()`
- Les ventes `progress` réservent le stock ; les ventes `paid` en consomment définitivement

---

## Finance

- **Caisse** (`/caisse`) : bilan de caisse par période, multi-devises (FC/USD), calcul de marge brute. Admin uniquement.
- **Crédit** (`/credit`) : sorties de caisse (dépenses diverses)
- **Débit** (`/debit`) : entrées de caisse (recettes hors ventes), avec génération PDF de reçu
- **Taux** (`/taux`) : un seul taux de change actif à la fois — la création d'un nouveau taux désactive l'ancien

---

## Paie et RH

- `Employe` : salaire journalier × nombre de jours + primes - déductions = total
- `PaieEmploye` a une contrainte d'unicité sur (employe + paie) : un employé ne peut être payé qu'une fois par période
- Génération de fiche de paie PDF via `PdfService`

---

## PDF

Deux services coexistent :
- **`FPdfGenerator`** — génère les factures ventes (`generateInvoicePdf`) et les reçus débit (`generateVersementPdf`) via la librairie FPDF
- **`PdfService`** — wrappeur Dompdf, utilisé pour les fiches de paie (HTML → PDF)

---

## Système d'alertes

### Vue d'ensemble
Système d'alertes configurables via une interface admin, affiché dans la cloche du header et dans la sidebar sur toutes les pages.

### Entité `AlerteConfig`
Un seul enregistrement en base (créé automatiquement avec les valeurs par défaut si absent).

| Champ | Type | Défaut | Description |
|-------|------|--------|-------------|
| `actifStockBas` | bool | `true` | Active/désactive les alertes stock bas |
| `actifPeremption` | bool | `true` | Active/désactive les alertes de péremption |
| `joursAvantPeremption` | int | `30` | Délai avant péremption pour déclencher l'alerte |

### Service `AlerteService`
`App\Service\AlerteService::getAlertes()` retourne :
```php
[
    'stockBas'    => [...],  // produits avec stockDisponible <= minimum
    'peremptions' => [...],  // [produit, joursRestants, expire]
    'total'       => int,    // stockBas + peremptions
    'config'      => AlerteConfig,
]
```

### Twig Global `alertes`
`App\Twig\AlerteExtension` injecte `alertes` dans **toutes** les pages via `getGlobals()`. Calculé une seule fois par requête (cache interne). Utilisé par le header et la sidebar sans intervention des contrôleurs.

### Routes
| Route | URL | Accès |
|-------|-----|-------|
| `app_alerte_index` | `/alertes/` | Tous les utilisateurs connectés |
| `app_alerte_config` | `/alertes/config` | ROLE_ADMIN uniquement |

### Méthodes de repositories ajoutées
- `ApprovisionnementRepository::findProduitsStockBas()` — réutilise `stockProduitByDate()` sans filtre, retourne uniquement les produits avec `stockBas = 1`
- `ProduitsRepository::findByExpirationAlert(int $jours)` — retourne les `Produits` dont `preemption <= today + $jours`

### Comportement de la cloche (header)
- Point **rouge** animé si `alertes.total > 0`, **vert** sinon
- Dropdown affiche les alertes stock bas (icône orange) et péremptions (icône rouge/orange) en Twig pur (pas d'Alpine.js pour les données)
- Lien "Voir toutes les alertes" → `/alertes/`

### Sidebar
- Lien **Alertes** avec badge rouge indiquant le nombre total — visible par tous
- Lien **Config. alertes** — visible par ROLE_ADMIN uniquement

---

## Audit Log

L'entité `AuditLog` existe et la page `/audit` (admin uniquement) permet de filtrer par utilisateur, entité, action et plage de dates. **Aucun listener Doctrine n'alimente automatiquement cette table** — les logs sont créés manuellement dans les contrôleurs concernés.

---

## Pages d'erreur

Les pages d'erreur personnalisées sont dans `templates/bundles/TwigBundle/Exception/` :
- `error403.html.twig` — Accès refusé
- `error404.html.twig` — Page introuvable
- `error500.html.twig` — Erreur serveur

Pour les déclencher depuis un contrôleur : `throw $this->createAccessDeniedException()` (403) ou `throw $this->createNotFoundException()` (404).

---

## Conventions à respecter

- Ne jamais importer `PHPUnit\*` dans du code de production.
- Utiliser `\Exception` (PHP natif) dans les blocs `catch` des contrôleurs, pas `Doctrine\DBAL\Driver\Exception`.
- Utiliser `getUserIdentifier()` plutôt que `getUsername()` pour récupérer l'identifiant de l'utilisateur connecté.
- Toujours utiliser `findBy([], ['createdAt' => 'DESC'])` plutôt que `findAll()` quand un tri est souhaité (`findAll()` n'accepte pas de paramètres).
- Les dates nullables dans les contrôleurs : `$date = $raw ? new \DateTime($raw) : new \DateTime('today')` (le `??` ne fonctionne pas sur `new \DateTime()`).
- `#[IsGranted]` doit être importé via `use Symfony\Component\Security\Http\Attribute\IsGranted`.
- Préférer `#[IsGranted('ROLE_ADMIN')]` sur la méthode plutôt que `denyAccessUnlessGranted()` dans le corps — cohérence avec le reste du projet.

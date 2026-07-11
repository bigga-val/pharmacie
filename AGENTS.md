# AGENTS.md

This file provides guidance to Codex (Codex.ai/code) when working with code in this repository.

## Project Overview

Afya is a full-stack enterprise business management web application built with **Symfony 6.1** and **Doctrine ORM**. It manages orders, inventory, employees, payroll, sales, and financial tracking. The app appears to serve a retail/restaurant/supply chain business.

## Common Commands

### PHP / Symfony
```bash
composer install                          # Install PHP dependencies
php bin/console server:run                # Start development server
php bin/console doctrine:migrations:migrate  # Run database migrations
php bin/console doctrine:migrations:diff     # Generate migration from entity changes
php bin/console doctrine:fixtures:load    # Load test data fixtures
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
- **Controller/** — one per domain. Use `#[Route]` attributes for routing. Controllers render Twig templates or return JSON/redirects.
- **Entity/** — Doctrine ORM entities. Changes to entities require generating and running a migration.
- **Repository/** — One repository per entity. Custom queries go here, not in controllers.
- **Form/** — Symfony FormType classes bound to entities.
- **Service/** — `PdfService` and `FPdfGenerator` for PDF generation (invoices/reports).
- **Security/** — `LoginFormAuthenticator` (form-based login) and `EmailVerifier` (registration confirmation).

### Key Domain Entities
- **Order flow:** `Commande` → `CommandeApprobateur` (approval) → `CommandeReception` (receiving) → `CommandeProduit` (line items)
- **Inventory:** `Produits`, `CategorieProduit`, `Approvisionnement`
- **Sales:** `Vente`, `ProduitVendu`
- **Finance:** `Credit`, `Debit`, `Taux` (exchange rates)
- **HR/Payroll:** `Employe`, `Paie`, `PaieEmploye`
- **Other:** `Table` (restaurant table management), `User` (authentication)

### Templates (`templates/`)
Organized by domain, mirroring the Controller structure. Shared layout components are in `templates/components/layout/`. Twig templates pull in Tailwind CSS utility classes.

### Frontend
Tailwind CSS 3.2 with a custom color palette (`primary`, `secondary`, `success`, `danger`, `warning`, `info`, `dark`) and the Nunito font. Also includes Bootstrap and Toastr (notifications) served from `public/assets/`. The `tailwind.config.js` scans `./templates/**/*.twig` for class names.

### Authentication
Form-based login via `LoginFormAuthenticator`. After login, users are redirected to `app_set_sessions` (a custom session setup route). Registration includes email verification via `EmailVerifier`.

- **Access control:** `ROLE_ADMIN` is required for sensitive routes (dashboard, ajustement stock, gestion utilisateurs). Standard authenticated users can only edit their own account.
- **Default password** for new users: `Afya@{année}` (ex: `Afya@2025`), affiché dans le flash message à la création. L'utilisateur doit le changer via `/reset_password`.

### Database
MySQL/MariaDB database named `db_credol`. Connection configured in `.env` (`DATABASE_URL`). Doctrine handles schema via migrations in `migrations/`.

### Async / Messaging
Symfony Messenger configured with a Doctrine transport (see `config/packages/messenger.yaml`).

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

### Entité `Approvisionnement` — champs notables
| Champ | Type | Description |
|---|---|---|
| `qty` | float | Quantité (peut être négative) |
| `cout` | float | Coût total (qty × prixUnitaire), 0 pour les ajustements |
| `taux` | float | Taux de change actif au moment de l'opération |
| `type` | string | `approvisionnement` ou `ajustement` |
| `motif` | string | Motif de l'ajustement (nullable) |

---

## Pages d'erreur

Les pages d'erreur personnalisées sont dans `templates/bundles/TwigBundle/Exception/` :
- `error403.html.twig` — Accès refusé
- `error404.html.twig` — Page introuvable
- `error500.html.twig` — Erreur serveur

Ces pages sont servies automatiquement par Symfony en mode production. Pour les déclencher proprement depuis un contrôleur, utiliser `throw $this->createAccessDeniedException()` plutôt qu'une redirection vers une route manuelle.

---

## Conventions à respecter

- Ne jamais importer `PHPUnit\*` dans du code de production.
- Utiliser `\Exception` (PHP natif) dans les blocs `catch` des contrôleurs, pas `Doctrine\DBAL\Driver\Exception`.
- Utiliser `getUserIdentifier()` plutôt que `getUsername()` pour récupérer l'identifiant de l'utilisateur connecté (cohérence).
- Toujours utiliser `findBy([], ['createdAt' => 'DESC'])` plutôt que `findAll()` quand un tri est souhaité (`findAll()` n'accepte pas de paramètres).
- Les dates nullables dans les contrôleurs : `$date = $raw ? new \DateTime($raw) : new \DateTime('today')` (le `??` ne fonctionne pas sur `new \DateTime()`).
- `#[IsGranted]` doit être importé via `use Symfony\Component\Security\Http\Attribute\IsGranted`.

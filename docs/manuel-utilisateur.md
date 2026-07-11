# Manuel Utilisateur — Afya

> **Version :** 1.1  
> **Date :** Mai 2026  
> **Application :** Afya — Gestion d'entreprise

---

## Table des matières

1. [Introduction](#1-introduction)
2. [Connexion et accès](#2-connexion-et-accès)
3. [Tableau de bord](#3-tableau-de-bord)
4. [Ventes et facturation](#4-ventes-et-facturation)
5. [Historique des ventes](#5-historique-des-ventes)
6. [Gestion des produits](#6-gestion-des-produits)
7. [Catégories de produits](#7-catégories-de-produits)
8. [Commandes fournisseurs](#8-commandes-fournisseurs)
9. [Approvisionnement et stock](#9-approvisionnement-et-stock)
10. [Caisse et flux de trésorerie](#10-caisse-et-flux-de-trésorerie)
11. [Gestion des employés](#11-gestion-des-employés)
12. [Paie et salaires](#12-paie-et-salaires)
13. [Taux de change](#13-taux-de-change)
14. [Gestion des tables](#14-gestion-des-tables)
15. [Gestion des utilisateurs](#15-gestion-des-utilisateurs)
16. [Rôles et permissions](#16-rôles-et-permissions)
17. [Questions fréquentes](#17-questions-fréquentes)

---

## 1. Introduction

**Afya** est une application de gestion d'entreprise complète qui couvre :

- La **vente** et la facturation (avec impression PDF)
- La gestion des **produits** et des **catégories**
- Les **commandes fournisseurs** avec workflow d'approbation
- L'**approvisionnement** et le suivi des stocks
- La **caisse** (entrées et sorties de trésorerie)
- La gestion des **employés** et de la **paie**
- Le suivi des **taux de change** FC / USD
- La gestion des **tables** (contexte restaurant / bar)

L'application supporte deux devises : le **Franc Congolais (FC)** et le **Dollar Américain (USD)**.

---

## 2. Connexion et accès

### 2.1 Se connecter

1. Ouvrez l'application dans votre navigateur.
2. Sur la page de connexion, saisissez votre **adresse e-mail** et votre **mot de passe**.
3. Cliquez sur **Se connecter**.

> Si vous n'avez pas encore de compte, contactez votre administrateur pour qu'il en crée un.

### 2.2 Se déconnecter

- Cliquez sur votre nom ou l'icône de profil en haut à droite.
- Sélectionnez **Se déconnecter**.

### 2.3 Première connexion

Lors de votre première connexion, l'application peut vous demander de configurer votre session. Suivez les instructions affichées à l'écran.

---

## 3. Tableau de bord

**Accessible à :** administrateurs uniquement

Le tableau de bord est la page d'accueil de l'application. Il affiche une synthèse en temps réel de l'activité de l'entreprise.

### 3.1 Indicateurs clés (KPI)

| Indicateur | Description |
|---|---|
| **CA Hier** | Chiffre d'affaires réalisé la veille, avec variation en % |
| **CA du mois** | Chiffre d'affaires du mois en cours |
| **Transactions du mois** | Nombre de ventes effectuées ce mois |
| **Commandes en attente** | Nombre de commandes fournisseurs en attente d'approbation |
| **Masse salariale** | Total des salaires versés ce mois |
| **Flux de trésorerie** | Solde global (crédits et débits) |
| **Employés** | Nombre total d'employés actifs |

### 3.2 Graphiques

- **Tendance des revenus (6 mois)** — courbe d'évolution du chiffre d'affaires sur les 6 derniers mois.
- **Ventes journalières (mois en cours)** — graphique en barres des ventes jour par jour.
- **Top 5 des produits** — les 5 produits les plus vendus, avec filtre de période (champ **Du** / **Au**) pour analyser n'importe quelle plage de dates.

### 3.3 Activité récente

En bas du tableau de bord, vous trouverez :

- Les **5 dernières ventes** effectuées.
- Les **5 dernières commandes** passées.
- Les **derniers journaux d'audit** (actions système).

### 3.4 Accès Finance (admin uniquement)

Le lien **Finance** dans le menu donne accès à un tableau de bord analytique avancé avec des tendances journalières, hebdomadaires, mensuelles et annuelles.

---

## 4. Ventes et facturation

**Accessible à :** tous les rôles

### 4.1 Créer une nouvelle vente

1. Dans le menu latéral, cliquez sur **Vente** → **Vendre**.
2. L'écran de vente s'affiche avec les champs suivants :

| Champ | Description |
|---|---|
| **Table** | Sélectionnez la table du client (si applicable) |
| **Devise** | Choisissez FC ou USD |
| **Produit** | Recherchez et sélectionnez un produit |
| **Quantité** | Saisissez la quantité vendue |
| **Prix unitaire** | Renseigné automatiquement selon le produit |

3. Ajoutez autant de lignes de produits que nécessaire.
4. Vérifiez le **total** affiché en bas.
5. Cliquez sur **Enregistrer** pour finaliser la vente.

> Le numéro de facture est généré automatiquement (ex : V00001, V00002...).

### 4.2 Vente avec prépaiement

Pour enregistrer une vente déjà payée à l'avance, utilisez l'option **Vente Prépayée** si disponible sur l'écran de vente.

### 4.3 Confirmer une vente (marquer comme payée)

1. Allez dans **Vente** → **Factures**.
2. Trouvez la vente concernée.
3. Cliquez sur **Confirmer** pour marquer la vente comme payée.

### 4.4 Annuler une vente

1. Allez dans **Vente** → **Factures**.
2. Trouvez la vente à annuler.
3. Cliquez sur **Annuler**.

> Une vente annulée ne peut pas être relancée. Créez une nouvelle vente si nécessaire.

### 4.5 Modifier une vente

1. Allez dans **Vente** → **Factures**.
2. Cliquez sur la vente à modifier, puis sur **Modifier**.
3. Effectuez vos changements et cliquez sur **Enregistrer**.

### 4.6 Imprimer / Exporter une facture en PDF

1. Allez dans **Vente** → **Factures**.
2. Cliquez sur la vente souhaitée pour afficher ses détails.
3. Cliquez sur **Imprimer** ou **Télécharger PDF**.

### 4.7 Consulter la liste des factures

Allez dans **Vente** → **Factures** pour voir toutes les factures avec leur statut (en cours, payée, annulée).

---

## 5. Historique des ventes

**Accessible à :** tous les rôles

### 5.1 Voir l'historique des produits vendus

1. Allez dans **Vente** → **Historique**.
2. Vous voyez la liste de tous les articles vendus, avec la date, le produit, la quantité et le prix.

### 5.2 Filtrer par période

- Utilisez les champs **Date de début** et **Date de fin** pour filtrer l'historique sur une période spécifique.
- Cliquez sur **Filtrer** pour appliquer.

---

## 6. Gestion des produits

**Accessible à :** tous les rôles sauf `ROLE_COMMANDE`

### 6.1 Voir la liste des produits

Allez dans **Produit** → **Liste**. Vous y trouverez :

- Le **code produit** (généré automatiquement)
- Le **nom** du produit
- La **catégorie**
- Le **prix de vente**
- Le **niveau de stock** actuel

### 6.2 Ajouter un nouveau produit

1. Allez dans **Produit** → **Nouveau Produit**.
2. Remplissez le formulaire :

| Champ | Description |
|---|---|
| **Nom** | Nom du produit |
| **Catégorie** | Catégorie à laquelle appartient le produit |
| **Prix de vente** | Prix unitaire de vente |
| **Unité de mesure** | Ex : pièce, kg, litre... |
| **Stock minimum** | Seuil d'alerte de stock bas |
| **Stock maximum** | Quantité maximale à stocker |
| **Image** | Photo du produit (optionnel) |

3. Cliquez sur **Enregistrer**.

> Le code produit est généré automatiquement par le système.

### 6.3 Voir les détails d'un produit

Cliquez sur le nom d'un produit dans la liste pour afficher sa fiche complète, incluant le niveau de stock actuel.

### 6.4 Exporter les produits en Excel

Dans la liste des produits, cliquez sur le bouton **Exporter Excel** pour télécharger la liste complète au format Excel.

---

## 7. Catégories de produits

**Accessible à :** administrateurs uniquement

### 7.1 Voir les catégories

Allez dans **Catégorie Produit** → **Liste des Catégories**.

### 7.2 Créer une nouvelle catégorie

1. Allez dans **Catégorie Produit** → **Nouvelle Catégorie**.
2. Saisissez le **nom** de la catégorie.
3. Cliquez sur **Enregistrer**.

> Vous pouvez aussi créer une catégorie directement depuis la liste via le formulaire intégré en haut de page.

---

## 8. Commandes fournisseurs

**Accessible à :** tous les rôles

Cette section gère les commandes passées auprès des fournisseurs, avec un **workflow d'approbation** en plusieurs étapes.

### 8.1 Cycle de vie d'une commande

```
Brouillon → Soumise → Approuvée / Rejetée → Réception des articles
```

### 8.2 Créer une nouvelle commande

1. Allez dans **Commande Produit** → **Commander**.
2. L'écran de commande s'affiche. Ajoutez les produits à commander :

| Champ | Description |
|---|---|
| **Produit** | Sélectionnez le produit à commander |
| **Quantité** | Quantité souhaitée |
| **Prix unitaire** | Prix d'achat estimé |

3. Ajoutez autant de lignes que nécessaire.
4. Cliquez sur **Enregistrer en brouillon** pour sauvegarder sans soumettre, ou sur **Soumettre** pour envoyer à l'approbation.

> Le numéro de commande est généré automatiquement (ex : COM00001).

### 8.3 Soumettre une commande pour approbation

Si vous avez sauvegardé une commande en brouillon :

1. Allez dans **Commande Produit** → **Historique**.
2. Trouvez votre commande avec le statut **Brouillon**.
3. Cliquez sur **Soumettre**.

### 8.4 Approuver ou rejeter une commande (approbateurs uniquement)

1. Allez dans **Commande Produit** → **Historique**.
2. Trouvez la commande avec le statut **Soumise**.
3. Cliquez sur **Approuver** ou **Rejeter**.
4. En cas de rejet, saisissez le motif si demandé.

> Seuls les utilisateurs désignés comme **approbateurs** peuvent approuver ou rejeter des commandes.

### 8.5 Gérer les approbateurs (admin uniquement)

1. Allez dans **Commande Produit** → **Approbateur**.
2. Ajoutez ou retirez des utilisateurs de la liste des approbateurs.

### 8.6 Réceptionner une commande

Après approbation, lorsque les articles arrivent :

1. Ouvrez la commande approuvée.
2. Dans la section **Réception**, confirmez les quantités reçues pour chaque ligne.
3. Cliquez sur **Confirmer la réception**.

### 8.7 Annuler une commande

Une commande en brouillon ou soumise peut être annulée :

1. Ouvrez la commande.
2. Cliquez sur **Annuler la commande**.

### 8.8 Imprimer une commande

Depuis le détail d'une commande, cliquez sur **Imprimer** pour générer un bon de commande imprimable.

### 8.9 Modifier ou supprimer une commande

- Une commande en **brouillon** peut être modifiée ou supprimée.
- Une commande **soumise ou approuvée** ne peut plus être modifiée.

---

## 9. Approvisionnement et stock

**Accessible à :** administrateurs uniquement

### 9.1 Ajouter du stock (approvisionner)

1. Allez dans **Approvisionnement** → **Approvisionner**.
2. Remplissez le formulaire :

| Champ | Description |
|---|---|
| **Produit** | Produit à réapprovisionner |
| **Quantité** | Quantité ajoutée au stock (peut être négative pour une correction) |
| **Prix unitaire** | Coût d'achat unitaire (calculé automatiquement selon le produit) |
| **Coût total** | Calculé automatiquement — non modifiable |

3. Cliquez sur **Enregistrer**.

### 9.2 Ajustement de stock

L'ajustement de stock permet de **corriger manuellement le niveau de stock** sans passer par un achat fournisseur. Il est utile pour enregistrer des pertes, vols, ou corrections après inventaire physique.

1. Allez dans **Approvisionnement** → **Ajustement stock**.
2. Remplissez le formulaire :

| Champ | Description |
|---|---|
| **Produit** | Produit à ajuster |
| **Quantité** | Positive = augmentation du stock / Négative = réduction du stock |
| **Motif** | Raison de l'ajustement |

3. Cliquez sur **Enregistrer l'ajustement**.

**Motifs disponibles :**
- Correction inventaire
- Perte
- Vol
- Produit avarié
- Retour client
- Autre

> Les ajustements apparaissent dans la liste des approvisionnements avec le badge **Ajustement** (orange), contrairement aux approvisionnements réels qui affichent le badge **Appro.** (bleu).

### 9.3 Voir l'historique des approvisionnements

Allez dans **Approvisionnement** → **Historique** pour consulter tous les mouvements de stock (approvisionnements et ajustements). La colonne **Type** permet de distinguer les deux.

### 9.4 Voir les niveaux de stock actuels

1. Allez dans **Approvisionnement** → **Stock**.
2. Vous voyez le stock disponible par produit.
3. Utilisez les filtres **Date de début** et **Date de fin** pour analyser le stock sur une période donnée.

> Un stock en dessous du seuil minimum apparaît en **rouge** (alerte de stock bas).

---

## 10. Caisse et flux de trésorerie

**Accessible à :** administrateurs uniquement

### 10.1 Bilan de caisse

Allez dans **Caisse** → **Bilan de caisse** pour consulter l'état de la trésorerie :

| Section | Description |
|---|---|
| **Entrées** | Total des ventes + débits enregistrés |
| **Sorties** | Total des crédits + salaires versés |
| **Solde** | Entrées − Sorties |

Le bilan est affiché séparément pour chaque devise (**FC** et **USD**).

Utilisez les filtres de date pour analyser une période spécifique.

### 10.2 Enregistrer une entrée de caisse (Débit)

Un **débit** représente une entrée d'argent dans la caisse autre que les ventes (ex : un apport de fonds, un remboursement reçu).

1. Allez dans **Caisse** → **Nouvelle Entrée**.
2. Remplissez :

| Champ | Description |
|---|---|
| **Montant** | Somme reçue |
| **Devise** | FC ou USD |
| **Motif** | Raison de l'entrée |
| **Date** | Date de l'opération |

3. Cliquez sur **Enregistrer**.

### 10.3 Consulter les entrées récentes

Allez dans **Caisse** → **Récentes Entrées** pour voir les dernières entrées enregistrées, avec possibilité de filtrer par date.

### 10.4 Enregistrer une sortie de caisse (Crédit)

Un **crédit** représente une sortie d'argent de la caisse (ex : achat de fournitures, frais divers).

1. Allez dans **Caisse** → **Nouvelle Sortie**.
2. Remplissez :

| Champ | Description |
|---|---|
| **Montant** | Somme dépensée |
| **Devise** | FC ou USD |
| **Motif** | Raison de la dépense |
| **Date** | Date de l'opération |

3. Cliquez sur **Enregistrer**.

### 10.5 Consulter les sorties récentes

Allez dans **Caisse** → **Récentes Sorties** pour voir les dernières sorties enregistrées.

---

## 11. Gestion des employés

**Accessible à :** administrateurs uniquement

### 11.1 Voir la liste des employés

Allez dans **Employe** → **Liste des Employés** pour voir tous les employés actifs avec leurs informations de base.

### 11.2 Ajouter un nouvel employé

1. Allez dans **Employe** → **Nouvel Employé**.
2. Remplissez le formulaire :

| Champ | Description |
|---|---|
| **Nom** | Nom de l'employé |
| **Prénom** | Prénom de l'employé |
| **Poste / Fonction** | Titre du poste occupé |
| **Date d'embauche** | Date de début de contrat |
| **Salaire de base** | Montant du salaire mensuel |
| **Devise du salaire** | FC ou USD |

3. Cliquez sur **Enregistrer**.

### 11.3 Voir le profil d'un employé

Cliquez sur le nom d'un employé dans la liste pour afficher sa fiche complète, incluant l'historique des paiements de salaire.

### 11.4 Modifier les informations d'un employé

1. Ouvrez la fiche de l'employé.
2. Cliquez sur **Modifier**.
3. Effectuez les changements nécessaires.
4. Cliquez sur **Enregistrer**.

### 11.5 Payer le salaire d'un employé

1. Ouvrez la fiche de l'employé.
2. Cliquez sur **Payer le salaire**.
3. Sélectionnez la **période de paie** concernée.
4. Confirmez le paiement.

> Le système empêche de payer deux fois le salaire d'un même employé pour la même période.

---

## 12. Paie et salaires

**Accessible à :** administrateurs uniquement

### 12.1 Voir les périodes de paie

Allez dans **Employe** → **Listes des Frais** pour voir toutes les périodes de paie créées (ex : Janvier 2026, Février 2026...).

### 12.2 Créer une nouvelle période de paie

1. Allez dans **Employe** → **Listes des Frais**.
2. Cliquez sur **Nouvelle Période**.
3. Saisissez le **nom** de la période (ex : "Mars 2026") et les dates de début/fin.
4. Cliquez sur **Enregistrer**.

### 12.3 Voir le détail d'une période de paie

Cliquez sur une période pour voir tous les employés payés lors de cette période et les montants versés.

### 12.4 Modifier ou supprimer une période

- Cliquez sur **Modifier** pour changer les informations d'une période.
- Cliquez sur **Supprimer** pour effacer une période (uniquement si aucun paiement n'y est attaché).

---

## 13. Taux de change

**Accessible à :** administrateurs uniquement

Le taux de change actif est utilisé dans tout le système pour convertir les montants entre FC et USD.

### 13.1 Voir les taux récents

Allez dans **Taux** → **Taux Récents** pour voir l'historique des taux de change enregistrés.

### 13.2 Mettre à jour le taux actif

1. Allez dans **Taux** → **Taux Récents**.
2. Cliquez sur **Nouveau Taux** ou **Modifier le taux actif**.
3. Saisissez le nouveau taux (ex : 1 USD = 2800 FC).
4. Marquez-le comme **actif**.
5. Cliquez sur **Enregistrer**.

> Un seul taux peut être **actif** à la fois. L'ancien taux actif est automatiquement désactivé.

---

## 14. Gestion des tables

**Accessible à :** administrateurs uniquement

Cette section est utile dans un contexte **restaurant ou bar** pour associer des ventes à des tables.

### 14.1 Voir les tables

Allez dans **Tables** → **Tables** pour voir la liste de toutes les tables configurées.

### 14.2 Ajouter une table

1. Dans la liste des tables, cliquez sur **Nouvelle Table**.
2. Saisissez le **numéro ou nom** de la table.
3. Cliquez sur **Enregistrer**.

### 14.3 Modifier ou supprimer une table

- Cliquez sur **Modifier** pour changer le nom d'une table.
- Cliquez sur **Supprimer** pour retirer une table (uniquement si aucune vente active ne lui est associée).

---

## 15. Gestion des utilisateurs

**Accessible à :** administrateurs uniquement

### 15.1 Voir la liste des utilisateurs

Allez dans **Utilisateur** → **Liste des Utilisateurs** pour voir tous les comptes créés dans le système.

### 15.2 Créer un nouvel utilisateur

1. Allez dans **Utilisateur** → **Nouvel Utilisateur**.
2. Remplissez le formulaire :

| Champ | Description |
|---|---|
| **Nom d'utilisateur** | Identifiant de connexion |
| **Adresse e-mail** | Adresse e-mail de l'utilisateur |
| **Rôle** | Niveau d'accès (voir section 16) |

3. Cliquez sur **Enregistrer**.

> Un mot de passe par défaut est automatiquement attribué et affiché dans le message de confirmation (format : `Afya@{année}`). Communiquez-le à l'utilisateur et demandez-lui de le changer dès sa première connexion via **Réinitialiser le mot de passe**.

### 15.3 Voir et modifier le profil d'un utilisateur

- **Administrateur** : cliquez sur n'importe quel utilisateur dans la liste pour accéder à son profil, puis sur **Modifier** pour changer ses informations ou son rôle.
- **Utilisateur standard** : chaque utilisateur peut modifier **son propre compte** (nom, email) depuis son profil. Il ne peut pas modifier les comptes des autres.

### 15.4 Réinitialiser un mot de passe

1. Dans la liste des utilisateurs, cliquez sur **Réinit. MDP** en face de l'utilisateur concerné.
2. Saisissez le nouveau mot de passe.
3. Cliquez sur **Enregistrer**.

> Si un utilisateur réinitialise son propre mot de passe, il est automatiquement déconnecté et doit se reconnecter avec le nouveau mot de passe.

---

## 16. Rôles et permissions

L'application dispose de plusieurs niveaux d'accès. Chaque utilisateur se voit attribuer un rôle par l'administrateur.

### 16.1 Tableau des permissions par rôle

| Fonctionnalité | Utilisateur standard | ROLE_COMMANDE | Administrateur |
|---|:---:|:---:|:---:|
| Tableau de bord | ❌ | ❌ | ✅ |
| Créer une vente | ✅ | ✅ | ✅ |
| Voir les factures | ✅ | ✅ | ✅ |
| Historique des ventes | ✅ | ✅ | ✅ |
| Voir la liste des produits | ✅ | ❌ | ✅ |
| Ajouter / modifier un produit | ✅ | ❌ | ✅ |
| Créer une commande fournisseur | ✅ | ✅ | ✅ |
| Voir l'historique des commandes | ✅ | ✅ | ✅ |
| Approuver / rejeter une commande | ✅ (si approbateur) | ✅ (si approbateur) | ✅ |
| Gérer les approbateurs | ✅ | ❌ | ✅ |
| Approvisionnement & stock | ❌ | ❌ | ✅ |
| Ajustement de stock | ❌ | ❌ | ✅ |
| Caisse (entrées / sorties / bilan) | ❌ | ❌ | ✅ |
| Gestion des employés | ❌ | ❌ | ✅ |
| Gestion de la paie | ❌ | ❌ | ✅ |
| Catégories de produits | ❌ | ❌ | ✅ |
| Taux de change | ❌ | ❌ | ✅ |
| Gestion des tables | ❌ | ❌ | ✅ |
| Gestion des utilisateurs | ❌ | ❌ | ✅ |
| Modifier son propre compte | ✅ | ✅ | ✅ |
| Tableau de bord Finance | ❌ | ❌ | ✅ |

### 16.2 Description des rôles

**Utilisateur standard**
- Peut créer des ventes et gérer les factures.
- Peut créer des commandes fournisseurs et les soumettre à l'approbation.
- Peut voir et gérer le catalogue produits.
- N'a pas accès à la caisse, aux employés, ni aux paramètres système.

**ROLE_COMMANDE**
- Peut uniquement créer des ventes et des commandes fournisseurs.
- Ne peut pas accéder au catalogue produits ni gérer les approbateurs.
- Accès limité à l'essentiel des opérations quotidiennes.

**Administrateur (ROLE_ADMIN)**
- Accès complet à toutes les fonctionnalités.
- Gère les utilisateurs, les employés, la caisse, les stocks, les taux et les tables.
- Peut consulter les analyses financières avancées.
- Seul habilité à approuver des commandes sans être dans la liste des approbateurs.

---

## 17. Questions fréquentes

**Q : Je ne vois pas le menu "Produit" dans la barre latérale.**  
R : Ce menu est masqué pour les utilisateurs ayant le rôle `ROLE_COMMANDE`. Contactez votre administrateur si vous pensez avoir besoin de cet accès.

**Q : Je ne peux pas approuver une commande.**  
R : Pour approuver des commandes, vous devez être ajouté à la liste des approbateurs par un administrateur (menu **Commande Produit** → **Approbateur**).

**Q : Le système m'indique que le salaire a déjà été payé pour cette période.**  
R : Le système empêche les doublons de paiement. Si une erreur a été commise, contactez votre administrateur.

**Q : Comment savoir si le stock d'un produit est insuffisant ?**  
R : Dans la section **Stock** (Approvisionnement → Stock), les produits dont le stock est en dessous du seuil minimum sont mis en évidence. Une alerte peut aussi apparaître lors d'une vente si le stock est insuffisant.

**Q : Puis-je modifier une facture après confirmation ?**  
R : Une fois confirmée, une facture ne peut plus être modifiée directement. Annulez-la et créez une nouvelle vente si nécessaire.

**Q : Comment changer le taux de change ?**  
R : Allez dans **Taux** → **Taux Récents**, puis créez un nouveau taux et marquez-le comme actif. L'ancien taux sera automatiquement désactivé.

**Q : Où voir le journal des actions effectuées dans le système ?**  
R : Le tableau de bord affiche les dernières entrées du journal d'audit en bas de page. Pour un historique complet, contactez votre administrateur.

**Q : Quelle est la différence entre un approvisionnement et un ajustement de stock ?**  
R : Un **approvisionnement** correspond à un achat réel auprès d'un fournisseur (entrée de marchandise avec un coût). Un **ajustement** est une correction manuelle du stock pour refléter la réalité physique (perte, vol, erreur de comptage) — il n'a pas d'impact financier sur la caisse.

**Q : Puis-je entrer une quantité négative lors d'un approvisionnement ?**  
R : Oui, une quantité négative est autorisée et représente une réduction de stock (retour fournisseur, correction). Pour les corrections liées à des pertes ou vols, préférez utiliser la fonction **Ajustement de stock** qui permet de préciser le motif.

**Q : Je ne vois plus le tableau de bord depuis la mise à jour.**  
R : Le tableau de bord est désormais réservé aux administrateurs. Si vous avez besoin d'y accéder, contactez votre administrateur pour qu'il vous attribue le rôle `ROLE_ADMIN`.

**Q : Quel est le mot de passe par défaut d'un nouvel utilisateur ?**  
R : Le mot de passe par défaut est affiché dans le message de confirmation lors de la création du compte. Il est de la forme `Afya@{année}` (ex : `Afya@2026`). Changez-le dès la première connexion via **Réinitialiser le mot de passe**.

---

*Document rédigé pour Afya — Toute reproduction ou diffusion doit être autorisée par l'administrateur système.*

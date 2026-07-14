<?php

namespace App\DataFixtures;

use App\Entity\Approvisionnement;
use App\Entity\CategorieProduit;
use App\Entity\Credit;
use App\Entity\Debit;
use App\Entity\Employe;
use App\Entity\Paie;
use App\Entity\PaieEmploye;
use App\Entity\ProduitVendu;
use App\Entity\Produits;
use App\Entity\Taux;
use App\Entity\User;
use App\Entity\Vente;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // ── 1. UTILISATEURS ─────────────────────────────────────────────────────
        $admin = $this->makeUser('admin@afya.com',        'Admin',              ['ROLE_ADMIN'],        'No 1, Av. de la Santé',  $manager);
        $this->makeUser('pharmacien@afya.com', 'Jean-Pierre Mukendi', ['ROLE_PHARMACIEN'],  'No 12, Av. Kabinda',     $manager);
        $this->makeUser('infirmiere@afya.com', 'Marie Kabongo',       ['ROLE_INFIRMIERE'],  'No 45, Av. du Commerce', $manager);

        $manager->flush(); // flush users en premier (Taux.user en a besoin)

        // ── 2. TAUX DE CHANGE (USD → CDF) ────────────────────────────────────────
        $this->makeTaux(2750.0, false, new \DateTimeImmutable('-35 days'), $admin, $manager);
        $this->makeTaux(2760.0, false, new \DateTimeImmutable('-20 days'), $admin, $manager);
        $this->makeTaux(2780.0, false, new \DateTimeImmutable('-10 days'), $admin, $manager);
        $this->makeTaux(2800.0, true,  new \DateTimeImmutable('-2 days'),  $admin, $manager);

        // ── 3. CATÉGORIES ────────────────────────────────────────────────────────
        $catAbx  = $this->makeCat('Antibiotiques',               10.0, $manager);
        $catAnlg = $this->makeCat('Analgésiques / Antipyrétiques', 5.0, $manager);
        $catAinf = $this->makeCat('Anti-inflammatoires',           5.0, $manager);
        $catVit  = $this->makeCat('Vitamines & Suppléments',      15.0, $manager);
        $catPara = $this->makeCat('Antiparasitaires',              8.0, $manager);
        $catFong = $this->makeCat('Antifongiques',                10.0, $manager);
        $catDerm = $this->makeCat('Dermatologie',                 12.0, $manager);
        $catPed  = $this->makeCat('Pédiatrie',                     8.0, $manager);
        $catCard = $this->makeCat('Cardiologie',                  10.0, $manager);
        $catDiab = $this->makeCat('Diabétologie',                 10.0, $manager);
        $catMat  = $this->makeCat('Matériel médical',             15.0, $manager);
        $catHyg  = $this->makeCat('Hygiène & Soins',              15.0, $manager);

        // ── 4. PRODUITS ──────────────────────────────────────────────────────────
        // Prix d'achat en FC — le prix de vente est calculé automatiquement :
        // prix = prixAchat × (1 + categorie.Pourcentage / 100)
        // Antibiotiques (10%)
        $amx500  = $this->makeProd('Amoxicilline 500mg',               'Beecham',   'AMX500', 'cp',      200, 50,  '+18 months', $catAbx,  $manager,  560);
        $cip500  = $this->makeProd('Ciprofloxacine 500mg',             'Cipla',     'CIP500', 'cp',      150, 30,  '+24 months', $catAbx,  $manager, 1260);
        $mtr250  = $this->makeProd('Métronidazole 250mg',              'Medopharm', 'MTR250', 'cp',      300, 60,  '+12 months', $catAbx,  $manager,  336);
        // Analgésiques (5%)
        $par500  = $this->makeProd('Paracétamol 500mg',                'Gsk',       'PAR500', 'cp',      500, 100, '+24 months', $catAnlg, $manager,  112);
        // Anti-inflammatoires (5%)
        $ibu400  = $this->makeProd('Ibuprofène 400mg',                 'Medopharm', 'IBU400', 'cp',      300, 60,  '+18 months', $catAinf, $manager,  196);
        $dic050  = $this->makeProd('Diclofénac 50mg',                  'Novartis',  'DIC050', 'cp',      200, 40,  '+18 months', $catAinf, $manager,  224);
        // Vitamines (15%)
        $vtc1000 = $this->makeProd('Vitamine C 1000mg',                'Bayer',     'VTC100', 'cp',      400, 80,  '+36 months', $catVit,  $manager,  168);
        $znc020  = $this->makeProd('Zinc 20mg',                        'Solgar',    'ZNC020', 'cp',      300, 60,  '+36 months', $catVit,  $manager,  140);
        $acf005  = $this->makeProd('Acide folique 5mg',                'Medopharm', 'ACF005', 'cp',      400, 80,  '+24 months', $catVit,  $manager,   84);
        // Antiparasitaires (8%)
        $art020  = $this->makeProd('Artémether/Luméfantrine 20/120mg', 'Novartis',  'ART020', 'cp',      200, 40,  '+24 months', $catPara, $manager, 1960);
        $alb400  = $this->makeProd('Albendazole 400mg',                'Gsk',       'ALB400', 'cp',      150, 30,  '+24 months', $catPara, $manager,  700);
        // Antifongiques (10%)
        $flu150  = $this->makeProd('Fluconazole 150mg',                'Cipla',     'FLU150', 'cp',      100, 20,  '+18 months', $catFong, $manager, 2800);
        // Dermatologie (12%)
        $mic001  = $this->makeProd('Miconazole crème 2%',              'Janssen',   'MIC001', 'tube',    80,  15,  '+18 months', $catDerm, $manager, 5600);
        // Pédiatrie (8%)
        $syp120  = $this->makeProd('Sirop Paracétamol 120mg/5ml',      'Gsk',       'SYP120', 'flacon',  100, 20,  '+18 months', $catPed,  $manager, 3360);
        $amxsus  = $this->makeProd('Amoxicilline susp. 125mg/5ml',     'Beecham',   'AMX125', 'flacon',  80,  15,  '+12 months', $catPed,  $manager, 4480);
        // Cardiologie (10%)
        $aml005  = $this->makeProd('Amlodipine 5mg',                   'Pfizer',    'AML005', 'cp',      150, 30,  '+24 months', $catCard, $manager,  448);
        // Diabétologie (10%)
        $met500  = $this->makeProd('Metformine 500mg',                 'Medopharm', 'MET500', 'cp',      200, 40,  '+18 months', $catDiab, $manager,  336);
        // Matériel médical (15%)
        $ser005  = $this->makeProd('Seringues 5ml',                    'BD',        'SER005', 'pce',     500, 100, '+60 months', $catMat,  $manager,  700);
        $gnt100  = $this->makeProd("Gants d'examen latex (boîte 100)", 'Kimberly',  'GNT100', 'boîte',   50,  10,  '+60 months', $catMat,  $manager, 11200);
        // Hygiène (15%)
        $cot100  = $this->makeProd('Coton hydrophile 100g',            'Hartmann',  'COT100', 'rouleau', 100, 20,  '+36 months', $catHyg,  $manager, 2240);

        $manager->flush(); // flush catégories + produits

        // ── 5. APPROVISIONNEMENT ─────────────────────────────────────────────────
        // Prix d'achat unitaire en FC (≈ 60 % du prix de vente)
        // Lot 1 — il y a 2 mois (stock initial)
        $d1 = new \DateTimeImmutable('-60 days');
        foreach ([
            [$amx500,  500,  560], [$cip500,  300, 1260], [$mtr250,  600,  336],
            [$par500, 1000,  112], [$ibu400,  600,  196], [$dic050,  400,  224],
            [$vtc1000, 800,  168], [$znc020,  600,  140], [$acf005,  800,   84],
            [$art020,  400, 1960], [$alb400,  300,  700], [$flu150,  200, 2800],
            [$mic001,  150, 5600], [$syp120,  200, 3360], [$amxsus,  150, 4480],
            [$aml005,  300,  448], [$met500,  400,  336], [$ser005, 1000,  700],
            [$gnt100,  100,11200], [$cot100,  200, 2240],
        ] as [$prod, $qty, $pu]) {
            $this->makeAppro($prod, (float)$qty, (float)$pu, 2780.0, $d1, 'admin@afya.com', $manager);
        }

        // Lot 2 — il y a 1 mois (réapprovisionnement)
        $d2 = new \DateTimeImmutable('-30 days');
        foreach ([
            [$amx500,  300,  580], [$par500,  500,  112], [$art020, 200, 2000],
            [$ibu400,  300,  210], [$vtc1000, 400,  175], [$syp120, 100, 3500],
            [$ser005,  500,  720], [$gnt100,   50,11500],
        ] as [$prod, $qty, $pu]) {
            $this->makeAppro($prod, (float)$qty, (float)$pu, 2800.0, $d2, 'admin@afya.com', $manager);
        }

        // Lot 3 — semaine dernière
        $d3 = new \DateTimeImmutable('-7 days');
        foreach ([
            [$amx500, 200,  600], [$cip500, 150, 1300], [$par500, 300, 112],
            [$flu150, 100, 2900], [$met500, 200,  364],
        ] as [$prod, $qty, $pu]) {
            $this->makeAppro($prod, (float)$qty, (float)$pu, 2800.0, $d3, 'pharmacien@afya.com', $manager);
        }

        // Ajustement — produits abîmés
        $adj = new Approvisionnement();
        $adj->setProduit($par500)->setQty(-20.0)->setCout(0)->setTaux(2800.0)
            ->setType('ajustement')->setMotif('Produits abîmés')
            ->setApproDate(new \DateTime('-5 days'))
            ->setCreatedAt(new \DateTimeImmutable('-5 days'))
            ->setCreatedBy('admin@afya.com');
        $manager->persist($adj);

        // ── 6. EMPLOYES ──────────────────────────────────────────────────────────
        $emp1 = $this->makeEmploye('Jean-Pierre Mukendi', 'PHM-001', '2022-01-15', 'Cadre',     'Pharmacien responsable', 50.0, $manager);
        $emp2 = $this->makeEmploye('Marie Kabongo',       'CSR-002', '2023-03-01', 'Exécution', 'Caissière',              30.0, $manager);
        $emp3 = $this->makeEmploye('Patrick Ilunga',      'APH-003', '2023-06-10', 'Exécution', 'Aide-pharmacien',        25.0, $manager);
        $emp4 = $this->makeEmploye('Alice Mwamba',        'APH-004', '2024-01-08', 'Exécution', 'Aide-pharmacien',        25.0, $manager);

        // ── 7. PAIE ───────────────────────────────────────────────────────────────
        $paieAvr = $this->makePaie('Paie Avril 2026', 4, 2026, $manager);
        $paieMai = $this->makePaie('Paie Mai 2026',   5, 2026, $manager);
        $paieJun = $this->makePaie('Paie Juin 2026',  6, 2026, $manager);

        $manager->flush();

        // ── 8. PAIE EMPLOYE ───────────────────────────────────────────────────────
        // [employe, nbJours, tarifJour, prime, déduction]
        $grille = [
            [$emp1, 26, 50.0, 200.0, 50.0],
            [$emp2, 26, 30.0, 100.0, 30.0],
            [$emp3, 26, 25.0,  50.0, 25.0],
            [$emp4, 26, 25.0,  50.0, 25.0],
        ];
        foreach ([$paieAvr, $paieMai, $paieJun] as $paie) {
            foreach ($grille as [$emp, $jours, $base, $prime, $ded]) {
                $pe = new PaieEmploye();
                $pe->setEmploye($emp)->setPaie($paie)->setNbJours($jours)
                   ->setSalaireBase($base * $jours)->setPrimes($prime)->setDeductions($ded)
                   ->setCreatedAt(new \DateTimeImmutable());
                $pe->calculerTotal();
                $manager->persist($pe);
            }
        }

        // ── 9. VENTES ─────────────────────────────────────────────────────────────
        $ventesConfig = [
            ['V2026-001', '-20 days', 'paid',     'caissier@afya.com'],
            ['V2026-002', '-18 days', 'paid',     'caissier@afya.com'],
            ['V2026-003', '-15 days', 'paid',     'caissier@afya.com'],
            ['V2026-004', '-12 days', 'paid',     'caissier@afya.com'],
            ['V2026-005', '-10 days', 'paid',     'caissier@afya.com'],
            ['V2026-006', '-8 days',  'paid',     'caissier@afya.com'],
            ['V2026-007', '-5 days',  'paid',     'caissier@afya.com'],
            ['V2026-008', '-3 days',  'paid',     'caissier@afya.com'],
            ['V2026-009', '-1 days',  'paid',     'caissier@afya.com'],
            ['V2026-010', 'today',    'progress', 'caissier@afya.com'],
        ];
        $ventes = [];
        foreach ($ventesConfig as [$num, $date, $status, $by]) {
            $v = new Vente();
            $v->setNumeroVente($num)->setVenteDate(new \DateTime($date))
              ->setStatusVente($status)->setCreatedBy($by)
              ->setCreatedAt(new \DateTimeImmutable($date));
            $manager->persist($v);
            $ventes[] = $v;
        }

        $manager->flush();

        // ── 10. PRODUITS VENDUS ───────────────────────────────────────────────────
        // prixUnitaire = prix de vente calculé du produit (prixAchat × (1 + marge%))
        // tauxMarge    = pourcentage de la catégorie au moment de la vente (snapshot)
        $lignes = [
            [0, $par500,  20], [0, $vtc1000, 10],
            [1, $art020,   6], [1, $alb400,   3],
            [2, $amx500,  20], [2, $par500,  10],
            [3, $ibu400,  15], [3, $dic050,  10],
            [4, $flu150,   5], [4, $mic001,   3],
            [5, $syp120,   4], [5, $par500,  30],
            [6, $cip500,  10], [6, $mtr250,  20],
            [7, $met500,  30], [7, $aml005,  30],
            [8, $ser005,  50], [8, $gnt100,   5],
            [9, $par500,  15], [9, $art020,   4],
        ];
        foreach ($lignes as [$vi, $prod, $qty]) {
            $pv = new ProduitVendu();
            $pv->setVente($ventes[$vi])->setProduit($prod)->setQty((float)$qty)
               ->setPrixUnitaire($prod->getPrix())
               ->setTauxMarge($prod->getCategorie()?->getPourcentage() ?? 0)
               ->setTaux(2800.0)
               ->setCreatedAt(new \DateTimeImmutable())->setCreatedby('caissier@afya.com');
            $manager->persist($pv);
        }

        // ── 11. CREDITS ───────────────────────────────────────────────────────────
        foreach ([
            [1500.0,    'Ventes semaine 1 mai',       '-25 days', 'USD', 2800.0],
            [2200.0,    'Ventes semaine 2 mai',        '-18 days', 'USD', 2800.0],
            [1800.0,    'Ventes semaine 3 mai',        '-11 days', 'USD', 2780.0],
            [2500.0,    'Ventes semaine 4 mai',        '-4 days',  'USD', 2800.0],
            [850000.0,  'Versement espèces mai (CDF)', '-3 days',  'CDF', 2800.0],
        ] as [$montant, $raison, $date, $devise, $tauxV]) {
            $c = new Credit();
            $c->setMontant($montant)->setRaison($raison)->setDevise($devise)->setTaux($tauxV)
              ->setDateCredit(new \DateTime($date))
              ->setCreatedAt(new \DateTimeImmutable($date))->setCreatedBy('admin@afya.com');
            $manager->persist($c);
        }

        // ── 12. DEBITS ────────────────────────────────────────────────────────────
        foreach ([
            [500.0,    'Loyer local',                   '-30 days', 'USD', 2800.0],
            [200.0,    'Eau & Électricité',              '-30 days', 'USD', 2800.0],
            [150.0,    'Frais de livraison fournisseur', '-28 days', 'USD', 2800.0],
            [80.0,     'Fournitures de bureau',          '-15 days', 'USD', 2800.0],
            [1400000.0,'Salaires avril 2026 (CDF)',      '-10 days', 'CDF', 2800.0],
        ] as [$montant, $raison, $date, $devise, $tauxV]) {
            $d = new Debit();
            $d->setMontant($montant)->setRaison($raison)->setDevise($devise)->setTaux($tauxV)
              ->setDateDebit(new \DateTime($date))
              ->setCreatedAt(new \DateTimeImmutable($date))->setCreatedBy('admin@afya.com');
            $manager->persist($d);
        }

        $manager->flush();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────────

    private function makeUser(string $email, string $username, array $roles, string $adresse, ObjectManager $m): User
    {
        $u = new User();
        $u->setEmail($email)->setUsername($username)->setRoles($roles)
          ->setAdressephysique($adresse)->setIsVerified(true)
          ->setPassword($this->hasher->hashPassword($u, 'Afya@2026'));
        $m->persist($u);
        return $u;
    }

    private function makeTaux(float $cout, bool $active, \DateTimeImmutable $at, User $user, ObjectManager $m): void
    {
        $t = new Taux();
        $t->setCout($cout)->setIsActive($active)->setCreatedAt($at)->setUser($user);
        $m->persist($t);
    }

    private function makeCat(string $designation, float $pct, ObjectManager $m): CategorieProduit
    {
        $c = new CategorieProduit();
        $c->setDesignation($designation)->setPourcentage($pct);
        $m->persist($c);
        return $c;
    }

    private function makeProd(
        string $designation, string $fabricant, string $code,
        string $unite, float $max, float $min, string $preemption,
        CategorieProduit $cat, ObjectManager $m, float $prixAchat = 0.0
    ): Produits {
        $prix = $prixAchat * (1 + ($cat->getPourcentage() ?? 0) / 100);
        $p = new Produits();
        $p->setDesignation($designation)->setFabricant($fabricant)
          ->setPrixAchat($prixAchat)->setPrix($prix)
          ->setCode($code)->setUniteMesure($unite)->setMaximum($max)->setMinimum($min)
          ->setPreemption(new \DateTime($preemption))->setCategorie($cat);
        $m->persist($p);
        return $p;
    }

    private function makeAppro(
        Produits $prod, float $qty, float $pu, float $taux,
        \DateTimeImmutable $date, string $by, ObjectManager $m
    ): void {
        $a = new Approvisionnement();
        $a->setProduit($prod)->setQty($qty)->setCout($qty * $pu)->setTaux($taux)
          ->setType('approvisionnement')
          ->setApproDate(\DateTime::createFromImmutable($date))
          ->setCreatedAt($date)->setCreatedBy($by);
        $m->persist($a);
    }

    private function makeEmploye(
        string $nom, string $matricule, string $embauche,
        string $cat, string $titre, float $salaireJ, ObjectManager $m
    ): Employe {
        $e = new Employe();
        $e->setNomcomplet($nom)->setMatricule($matricule)
          ->setDateembauche(new \DateTime($embauche))
          ->setCategorie($cat)->setTitre($titre)->setSalaireJournalier($salaireJ);
        $m->persist($e);
        return $e;
    }

    private function makePaie(string $label, int $month, int $year, ObjectManager $m): Paie
    {
        $p = new Paie();
        $p->setLabel($label)->setMonthPay($month)->setYearPay($year);
        $m->persist($p);
        return $p;
    }
}

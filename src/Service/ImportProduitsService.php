<?php

namespace App\Service;

use App\Entity\Produits;
use App\Repository\CategorieProduitRepository;
use App\Repository\ProduitsRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportProduitsService
{
    public function __construct(
        private EntityManagerInterface    $em,
        private CategorieProduitRepository $catRepo,
        private ProduitsRepository         $produitsRepo,
    ) {}

    /**
     * Traite un fichier Excel et retourne un rapport d'import.
     *
     * @return array{created: int, ignored: int, errors: array<int, array{ligne: int, designation: string, raison: string}>}
     */
    public function import(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true); // colonnes A, B, C...

        // Ignorer la ligne d'en-tête
        array_shift($rows);

        $created = 0;
        $errors  = [];
        $ligne   = 1; // commence à 2 dans Excel (1 = en-tête)

        foreach ($rows as $row) {
            $ligne++;

            $designation = trim((string)($row['A'] ?? ''));
            $code        = strtoupper(trim((string)($row['B'] ?? '')));
            $categorie   = trim((string)($row['C'] ?? ''));
            $prixAchat   = $row['D'] ?? null;
            $uniteMesure = trim((string)($row['E'] ?? ''));
            $minimum     = $row['F'] ?? null;
            $maximum     = $row['G'] ?? null;
            $fabricant   = trim((string)($row['H'] ?? ''));
            $preemption  = trim((string)($row['I'] ?? ''));

            // Ignorer les lignes vides
            if ($designation === '' && $code === '') {
                continue;
            }

            // Validation
            if ($designation === '') {
                $errors[] = ['ligne' => $ligne, 'designation' => '(vide)', 'raison' => 'Désignation manquante'];
                continue;
            }

            if ($code === '') {
                $errors[] = ['ligne' => $ligne, 'designation' => $designation, 'raison' => 'Code manquant'];
                continue;
            }

            if (!is_numeric($prixAchat) || (float)$prixAchat <= 0) {
                $errors[] = ['ligne' => $ligne, 'designation' => $designation, 'raison' => 'Prix d\'achat invalide ou manquant'];
                continue;
            }

            // Code déjà existant
            if ($this->produitsRepo->findOneBy(['code' => $code])) {
                $errors[] = ['ligne' => $ligne, 'designation' => $designation, 'raison' => "Code \"$code\" déjà existant en base"];
                continue;
            }

            // Catégorie
            if ($categorie === '') {
                $errors[] = ['ligne' => $ligne, 'designation' => $designation, 'raison' => 'Catégorie manquante'];
                continue;
            }

            $cat = $this->catRepo->findOneBy(['designation' => $categorie]);
            if (!$cat) {
                $errors[] = ['ligne' => $ligne, 'designation' => $designation, 'raison' => "Catégorie \"$categorie\" introuvable en base"];
                continue;
            }

            // Création du produit
            $prixAchatFloat = (float)$prixAchat;
            $pourcentage    = $cat->getPourcentage() ?? 0;
            $prix           = $prixAchatFloat * (1 + $pourcentage / 100);

            $produit = new Produits();
            $produit->setDesignation($designation)
                    ->setCode($code)
                    ->setCategorie($cat)
                    ->setPrixAchat($prixAchatFloat)
                    ->setPrix($prix)
                    ->setUniteMesure($uniteMesure ?: null)
                    ->setMinimum($minimum !== null && $minimum !== '' ? (float)$minimum : null)
                    ->setMaximum($maximum !== null && $maximum !== '' ? (float)$maximum : null)
                    ->setFabricant($fabricant ?: null);

            if ($preemption !== '') {
                try {
                    $produit->setPreemption(new \DateTime($preemption));
                } catch (\Exception) {
                    // date invalide — on ignore juste la préemption
                }
            }

            $this->em->persist($produit);
            $created++;
        }

        $this->em->flush();

        return [
            'created' => $created,
            'ignored' => count($errors),
            'errors'  => $errors,
        ];
    }
}

<?php

namespace App\Service;

use App\Entity\Approvisionnement;
use App\Repository\ProduitsRepository;
use App\Repository\TauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportApprovisionnementService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ProduitsRepository     $produitsRepo,
        private TauxRepository         $tauxRepo,
    ) {}

    /**
     * @return array{created: int, ignored: int, errors: array<int, array{ligne: int, code: string, raison: string}>}
     */
    public function import(string $filePath, string $createdBy): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true);

        array_shift($rows); // ignorer l'en-tête

        $tauxActif = $this->tauxRepo->findOneBy(['isActive' => true]);

        $created = 0;
        $errors  = [];
        $ligne   = 1;

        foreach ($rows as $row) {
            $ligne++;

            $code         = strtoupper(trim((string)($row['A'] ?? '')));
            $qty          = $row['B'] ?? null;
            $prixUnitaire = $row['C'] ?? null;
            $dateRaw      = trim((string)($row['D'] ?? ''));

            if ($code === '' && ($qty === null || $qty === '')) {
                continue;
            }

            if ($code === '') {
                $errors[] = ['ligne' => $ligne, 'code' => '(vide)', 'raison' => 'Code produit manquant'];
                continue;
            }

            if (!is_numeric($qty) || (float)$qty <= 0) {
                $errors[] = ['ligne' => $ligne, 'code' => $code, 'raison' => 'Quantité invalide ou manquante'];
                continue;
            }

            $produit = $this->produitsRepo->findOneBy(['code' => $code]);
            if (!$produit) {
                $errors[] = ['ligne' => $ligne, 'code' => $code, 'raison' => "Produit avec le code \"$code\" introuvable en base"];
                continue;
            }

            $qtyFloat   = (float)$qty;
            $prixFloat  = is_numeric($prixUnitaire) && (float)$prixUnitaire > 0
                ? (float)$prixUnitaire
                : ($produit->getPrixAchat() ?? 0);

            $approDate = new \DateTime('today');
            if ($dateRaw !== '') {
                try {
                    $approDate = new \DateTime($dateRaw);
                } catch (\Exception) {
                    // date invalide — on garde aujourd'hui
                }
            }

            $appro = new Approvisionnement();
            $appro->setProduit($produit)
                  ->setQty($qtyFloat)
                  ->setCout($qtyFloat * $prixFloat)
                  ->setTaux($tauxActif?->getCout())
                  ->setType('approvisionnement')
                  ->setApproDate($approDate)
                  ->setCreatedAt(new \DateTimeImmutable())
                  ->setCreatedBy($createdBy);

            $this->em->persist($appro);
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

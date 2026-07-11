<?php

namespace App\Controller;

use App\Repository\ApprovisionnementRepository;
use App\Repository\UserRepository;
use App\Service\FPdfGenerator;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/commande-appro')]
class CommandeApproController extends AbstractController
{
    #[Route('/', name: 'app_commande_appro')]
    public function index(
        ApprovisionnementRepository $approRepo,
        UserRepository $userRepo,
        Request $request,
        FPdfGenerator $pdfGenerator,
    ): Response {
        $stockBas = $approRepo->findProduitsStockBas();
        $admin = $userRepo->findFirstAdmin();
        $adminEmail = $admin?->getEmail() ?? '';

        if ($request->isMethod('POST')) {
            $designations = $request->request->all('designation');
            $quantites    = $request->request->all('quantite');
            $email        = trim($request->request->get('email', $adminEmail));
            $action       = $request->request->get('action', 'pdf');

            $produits = [];
            foreach ($designations as $idx => $designation) {
                $qty = (float)($quantites[$idx] ?? 0);
                if ($qty > 0 && trim($designation) !== '') {
                    $produits[] = [
                        'designation' => $designation,
                        'quantite'    => $qty,
                    ];
                }
            }

            if (empty($produits)) {
                $this->addFlash('warning', 'Aucun produit sélectionné.');
                return $this->redirectToRoute('app_commande_appro');
            }

            $createdBy  = $this->getUser()?->getUserIdentifier() ?? 'Système';
            $pdfContent = $pdfGenerator->generateCommandeApproPdf($produits, $createdBy);
            $filename   = 'commande-appro-' . date('Y-m-d') . '.pdf';

            if ($action === 'pdf') {
                return new Response($pdfContent, 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            }

            // Envoi par email
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->SMTPAuth    = true;
                $mail->SMTPSecure  = $this->getParameter('smtp_encryption');
                $mail->Host        = $this->getParameter('smtp_host');
                $mail->Port        = $this->getParameter('smtp_port');
                $mail->Username    = $this->getParameter('smtp_username');
                $mail->Password    = $this->getParameter('smtp_password');
                $mail->setFrom($this->getParameter('smtp_from'), 'Afya');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Bon de commande approvisionnement - ' . date('d/m/Y');
                $mail->Body    = '<p>Veuillez trouver en pièce jointe le bon de commande d\'approvisionnement du ' . date('d/m/Y') . '.</p>';
                $mail->addStringAttachment($pdfContent, $filename, 'base64', 'application/pdf');
                $mail->send();

                $this->addFlash('success', 'Bon de commande envoyé à ' . $email . ' avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'envoi : ' . $e->getMessage());
            }

            return $this->redirectToRoute('app_commande_appro');
        }

        return $this->render('commande_appro/new.html.twig', [
            'stockBas'   => $stockBas,
            'adminEmail' => $adminEmail,
        ]);
    }
}

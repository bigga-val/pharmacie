<?php

namespace App\Service;
use App\Entity\BonProduit;
use App\Repository\DebitRepository;
use App\Repository\LigneBonProduitRepository;
use App\Repository\ProduitVenduRepository;
use App\Repository\VenteRepository;
use FPDF;
use App\Repository\FactureRepository;
use App\Repository\LigneFactureRepository;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


class FPdfGenerator
{

    private $fPDF;

    public function  __construct()
    {
        $this->fPDF = new FPDF();
        $this->fPDF->AddPage();
        $this->fPDF->SetMargins(0, 0, 2);
//        $pdfOptions = new Options();
//        $pdfOptions->set('defaultFont', 'Garamond');
//        $this->domPDF->setOptions($pdfOptions);

    }
    public function generatePdf($factureID, FactureRepository $factureRepository, LigneFactureRepository $ligneFactureRepository)
    {
        $facture = $factureRepository->find($factureID);
        $lignes = $ligneFactureRepository->findBy([
            'facture'=>$factureID
        ]);
        $pdf = $this->fPDF;
        $mail = new PHPMailer();
        //$pdf->AddPage();

        $pdf->SetDrawColor(183); // Couleur du fond RVB
        $pdf->SetFillColor(221); // Couleur des filets RVB
        $pdf->SetTextColor(0); // Couleur du texte noir

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetY(20);
        $pdf->SetX(15);
        $pdf->Cell(40, 10, 'Facture No '.$facture->getNumeroFacture());

        $pdf->Image('assets/images/qazi.png', 147, 0, 50);

        // informations sur qazi
        // ligne 1
        $pdf->SetY(40);
        $pdf->SetX(90);
        $pdf->Cell(60,8,'13 Tetrick Road, Cypress Gardens, Florida, 33884, US',0,1,'L',0);

        // ligne 2
        $pdf->SetY(48);
        $pdf->SetX(165);
        $pdf->Cell(60,8,'info@qazi.com',0,1,'L',0);

        //ligne 3
        $pdf->SetY(56);
        $pdf->SetX(162);
        $pdf->Cell(60,8,'+243995053623',0,1,'L',0);

        //tracer une ligne horizontale
        $pdf->Line(15, 70, 195, 70);

        //Inclure les infos du client
        $pdf->SetY(75);
        $pdf->SetX(15);
        $pdf->Cell(60,8,'Info du Client :',0,1,'L',0);
        //nom du client
        $pdf->SetY(80);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getNom(),0,1,'L',0);
        //son adresse physique
        $pdf->SetY(85);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getAdressephysique(),0,1,'L',0);
        //son email
        $pdf->SetY(90);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getEmail(),0,1,'L',0);
        //son telephone
        $pdf->SetY(95);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getTelephone(),0,1,'L',0);

        //Infos de la facture
        //label numero facture
        $pdf->SetY(75);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Facture No',0,1,'L',0);

        //label date debut
        $pdf->SetY(80);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Date d\'etablissement',0,1,'L',0);
        //label date d'expiration
        $pdf->SetY(85);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Date d\'expiration',0,1,'L',0);
        //valeur numero facture
        $pdf->SetY(75);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getNumeroFacture(),0,1,'L',0);

        //valeur date etablissement
        $pdf->SetY(80);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getDateDebut()->format('d-m-Y'),0,1,'L',0);

        //valeur date expiration
        $pdf->SetY(85);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getDateFin()->format('d-m-Y'),0,1,'L',0);

        //tableau des ligne de la facture
        $tabY = 105;
        $prodX = 15; $qtyX = 57; $puX = 99; $totX=143;
        // 1° ligne du tableau


        $pdf->SetY($tabY);

        $pdf->SetX($prodX);
        $pdf->Cell(50,8,ucfirst($facture->getTypeBien()) ,1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
        // position de la colonne 2 (70 = 10+60)
        $pdf->SetX($qtyX);
        $pdf->Cell(50,8,'Quantite',1,0,'C',1);
        // position de la colonne 3 (130 = 70+60)
        $pdf->SetX($puX);
        $pdf->Cell(50,8,'Prix Unitaire',1,0,'C',1);

        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Total',1,0,'C',1);
        $soustotal = 0;
        foreach($lignes as $ligne){
            $tabY = $tabY +8;
            $pdf->SetY($tabY);

            $pdf->SetX($prodX);
            if($facture->getTypeBien() =='produit'){
                $pdf->Cell(50,8,$ligne->getProduit()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne

            }else{
                $pdf->Cell(50,8,$ligne->getService()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
            }

            // position de la colonne 2 (70 = 10+60)
            $pdf->SetX($qtyX);
            $pdf->Cell(50,8,$ligne->getQuantite(),1,0,'C',1);
            // position de la colonne 3 (130 = 70+60)
            $pdf->SetX($puX);
            $pdf->Cell(50,8,$ligne->getPrix(),1,0,'C',1);
            $total = $ligne->getQuantite() * $ligne->getPrix();
            $soustotal = $soustotal+$total;
            $pdf->SetX($totX);
            $pdf->Cell(50,8,$total,1,0,'C',1);
        }
        //Sous-total
        $tabY = $tabY +15;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Sous-Total: '.$soustotal,0,1,'R',0);
        //TVA-Tax
        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'TVA(%): '.$facture->getTax(),0,1,'R',0);

        //Reduction - Discount
        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Reduction(%): '.$facture->getDiscount(),0,1,'R',0);

        //Grand-Total
        $taxable = $soustotal +($soustotal* $facture->getTax())/100;
        $reductible = ($soustotal * $facture->getDiscount())/100;
        $grandtotal = $taxable - $reductible;
        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Grand-Total: '.$grandtotal,0,1,'R',0);


        //Visualiser dans le navigateur
        // email stuff (change data below)


       return $pdf->Output('S');
        ///comment the below code to readjust the pdf file
        ///
        //
        //return $pdf->Output('attachment.pdf', 'S');
//        $mail->AddStringAttachment($attachment, 'attachment.pdf');
//
//        $mail->isSMTP();
//        $mail->SMTPAuth = true;
//        $mail->SMTPSecure = 'ssl';
//        $mail->Host = 'smtp.hostinger.com';
//        $mail->Port = '465';
//        $mail->isHTML(true);
//        $mail->Username = 'admin@insoftware.tech';
//        $mail->Password = 'Insoft@123';
//        $mail->SetFrom('admin@insoftware.tech');
//        //$mail->addAttachment($myFile);
//
//        $mail->Subject = ucfirst('Votre facture est prete');
//        $mail->Body = $mailContent;
//        $mail->AddAddress($facture->getClient()->getEmail());
//        return $mail->Send();


        //return $this->senderEmail('Test d\'envoi de facture',$mailContent,'gabykatonge@isicom.education');
        //return $pdf->Output('S');

//        if(mail($to, $subject, $body, $headers)){
//            $_SESSION['send_ok'] = true;
//            header("Location: gererorg.php");
//        }else{
//            header("Location: historique.php");
//        }

        // Télécharger le PDF
        /*
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="votre_fichier.pdf"',
        ]);
        */
    }

    public function generateInvoicePdf($venteID, VenteRepository $venteRepository, ProduitVenduRepository $produitVenduRepository)
    {
        $vente = $venteRepository->find($venteID);
        $lignes = $produitVenduRepository->findBy([
            'vente'=>$venteID
        ]);
        $pdf = $this->fPDF;
        //$mail = new PHPMailer();
        //$pdf->AddPage();

        $pdf->SetDrawColor(183); // Couleur du fond RVB
        $pdf->SetFillColor(221); // Couleur des filets RVB
        $pdf->SetTextColor(0); // Couleur du texte noir

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetY(5);
        $pdf->SetX(15);
        $pdf->Cell(40, 10, 'Insoft Afya');

        //$pdf->Image('assets/images/qazi.png', 147, 0, 50);

        // Facture number label
        $pdf->SetY(10);
        $pdf->SetX(15);
        $pdf->Cell(40,8,'Facture No : '.$vente->getNumeroVente());
        //tracer une ligne horizontale
        //$pdf->Line(15, 20, 195, 20);
        $pdf->Line(15, 17, 80, 17);

        //tableau des lignes de la facture
        $tabY = 20;
        $prodX = 15; $qtyX = $prodX + 15; $totX=$qtyX+15;

        //1° ligne du tableau
        $pdf->SetY($tabY);
        $pdf->SetX($prodX);
        $pdf->Cell(20,4,'Produit' ,0,0,'C',0);  // 60 >largeur colonne, 8 >hauteur colonne
        //position de la colonne 2 (70 = 10+60)
        $pdf->SetX($qtyX);
        $pdf->Cell(20,4,'Quantite',0,0,'C',0);

        $pdf->SetX($totX);
        $pdf->Cell(20,4,'Total',0,0,'C',0);
        $soustotal = 0;
        $totalUSD = 0;
        $soustotalUSD = 0;


        foreach($lignes as $ligne){
            $tabY = $tabY +6;
            $pdf->SetY($tabY);
            ////Nom du produit
            $prodX = $prodX;
            $pdf->SetX($prodX);
            $pdf->Cell(20,4,$ligne->getProduit()->getDesignation(),0,0,'L',0);  // 60 >largeur colonne, 8 >hauteur colonne


            // position de la colonne 2 (70 = 10+60) qty de produits
            $qtyX = $prodX + 15;
            $pdf->SetX($qtyX);
            $pdf->Cell(20,4,$ligne->getQty(). ' '.$ligne->getProduit()->getUniteMesure(),0,0,'R',0);

            $total = $ligne->getQty() * $ligne->getPrixUnitaire();
            //Total ligne
            $totX=$qtyX+15;
            $pdf->SetX($totX);
            $pdf->Cell(20,4,$total,0,0,'R',0);

            $soustotal = $soustotal + $total;
            If($ligne->getTaux() != 0){
                $soustotalUSD = $total / $ligne->getTaux();
            }
            $totalUSD = $totalUSD + $soustotalUSD;
        }

        //label Valeur totale
        $tabY = $tabY+6;
        $pdf->SetY($tabY);
        $pdf->SetX(15);
        $pdf->Cell(60,8,'Total: '. $soustotal.' Fc -> '.number_format($totalUSD,2, '.', '') .' USD',0,1,'L',0);


        //label date etablissement
        $tabY = $tabY+6;
        $pdf->SetY($tabY);
        $pdf->SetX(15);
        $pdf->Cell(60,8,'Emis ',0,1,'L',0);

        $pdf->SetY($tabY);
        $pdf->SetX(30);
        $pdf->Cell(60,8,': '.$vente->getCreatedAt()->format('d-m-Y H:i:s') ,0,1,'L',0);

        //label date impression
        $tabY = $tabY+6;
        $pdf->SetY($tabY);
        $pdf->SetX(15);
        $pdf->Cell(60,8,'Imprime ',0,1,'L',0);

        //valeur date d'impression de facture
        $pdf->SetY($tabY);
        $pdf->SetX(30);
        $pdf->Cell(60,8,': '.date("d-m-Y H:i:s"),0,1,'L',0);




        $tabY = $tabY +6;
        $pdf->SetY($tabY);
        $pdf->SetX(15);
        $pdf->Cell(20,4,'Par : '.$vente->getCreatedBy(),0,0,'L',0);  // 60 >largeur colonne, 8 >hauteur colonne

        $tabY = $tabY +6;

        $pdf->SetY($tabY);
        $pdf->SetX(15);
        $pdf->Cell(20,4,'A Bientot',0,6,'C',0);  // 60 >largeur colonne, 8 >hauteur colonne

        //I: send the file inline to the browser. The PDF viewer is used if available.
        //D: send to the browser and force a file download with the name given by name.
        //F: save to a local file with the name given by name (may include a path).
        //S: return the document as a string.
        return $pdf->Output('S');
    }


    public function generateVersementPdf($debit, DebitRepository $debitRepository)
    {
        $debit = $debitRepository->find($debit);

        $pdf = $this->fPDF;
        //$mail = new PHPMailer();
        //$pdf->AddPage();

        $pdf->SetDrawColor(183); // Couleur du fond RVB
        $pdf->SetFillColor(221); // Couleur des filets RVB
        $pdf->SetTextColor(0); // Couleur du texte noir

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetY(5);
        $pdf->SetX(15);
        $pdf->Cell(40, 10, 'Insoft Afya');

        //$pdf->Image('assets/images/qazi.png', 147, 0, 50);

        // Facture number label
        $pdf->SetY(10);
        $pdf->SetX(15);
        $pdf->Cell(40,8,'Restau - Lounge Bar');
        //tracer une ligne horizontale
        //$pdf->Line(15, 20, 195, 20);
        $pdf->Line(15, 17, 80, 17);

        //tableau des lignes de la facture
        $tabY = 20;
        $prodX = 15; $qtyX = $prodX + 20; $totX=$qtyX+15;

        //1° ligne du tableau
        $pdf->SetY($tabY);
        $pdf->SetX($prodX);
        $pdf->Cell(20,4,'Montant' ,0,0,'C',0);  // 60 >largeur colonne, 8 >hauteur colonne
        //position de la colonne 2 (70 = 10+60)
        $pdf->SetX($qtyX);
        $pdf->Cell(20,4,'Taux',0,0,'C',0);


        //foreach($lignes as $ligne){
            $tabY = $tabY +6;
            $pdf->SetY($tabY);
            ////Nom du produit
            //$prodX = $prodX;
            $pdf->SetX($prodX);
            $pdf->Cell(20,4,$debit->getMontant() .' '.$debit->getDevise(),0,0,'L',0);  // 60 >largeur colonne, 8 >hauteur colonne


            // position de la colonne 2 (70 = 10+60) qty de produits
            $qtyX = $prodX + 15;
            $pdf->SetX($qtyX);
            $pdf->Cell(20,4,$debit->getTaux(),0,0,'R',0);



        //}

        //label Valeur totale
        $tabY = $tabY+6;
        $pdf->SetY($tabY);
        $pdf->SetX(15);
        //$pdf->Cell(60,8,'Total: '. $soustotal.' Fc -> '.number_format($totalUSD,2, '.', '') .' USD',0,1,'L',0);


        //label date etablissement
        $tabY = $tabY+6;
        $pdf->SetY($tabY);
        $pdf->SetX(15);
        $pdf->Cell(60,8,'Emis ',0,1,'L',0);

        $pdf->SetY($tabY);
        $pdf->SetX(30);
        $pdf->Cell(60,8,': '.$debit->getCreatedAt()->format('d-m-Y H:i:s') ,0,1,'L',0);

        //label date impression
        $tabY = $tabY+6;
        $pdf->SetY($tabY);
        $pdf->SetX(15);
        $pdf->Cell(60,8,'Imprime ',0,1,'L',0);

        //valeur date d'impression de facture
        $pdf->SetY($tabY);
        $pdf->SetX(30);
        $pdf->Cell(60,8,': '.date("d-m-Y H:i:s"),0,1,'L',0);




        $tabY = $tabY +6;
        $pdf->SetY($tabY);
        $pdf->SetX(15);
        $pdf->Cell(20,4,'Par : '.$debit->getCreatedBy(),0,0,'L',0);  // 60 >largeur colonne, 8 >hauteur colonne

        $tabY = $tabY +6;

        $pdf->SetY($tabY);
        $pdf->SetX(15);
        $pdf->Cell(20,4,'A Bientot',0,6,'C',0);  // 60 >largeur colonne, 8 >hauteur colonne

        //I: send the file inline to the browser. The PDF viewer is used if available.
        //D: send to the browser and force a file download with the name given by name.
        //F: save to a local file with the name given by name (may include a path).
        //S: return the document as a string.
        return $pdf->Output('S');
    }
    public function sendInvoiceByEMail($factureID, FactureRepository $factureRepository, LigneFactureRepository $ligneFactureRepository)
    {
        $facture = $factureRepository->find($factureID);
        $lignes = $ligneFactureRepository->findBy([
            'facture'=>$factureID
        ]);
        $pdf = $this->fPDF;
        $mail = new PHPMailer();
        //$pdf->AddPage();

        $pdf->SetDrawColor(183); // Couleur du fond RVB
        $pdf->SetFillColor(221); // Couleur des filets RVB
        $pdf->SetTextColor(0); // Couleur du texte noir

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetY(20);
        $pdf->SetX(15);
        $pdf->Cell(40, 10, 'Facture No '.$facture->getNumeroFacture());

        $pdf->Image('assets/images/qazi.png', 147, 0, 50);

        // informations sur qazi
        // ligne 1
        $pdf->SetY(40);
        $pdf->SetX(90);
        $pdf->Cell(60,8,'13 Tetrick Road, Cypress Gardens, Florida, 33884, US',0,1,'L',0);

        // ligne 2
        $pdf->SetY(48);
        $pdf->SetX(165);
        $pdf->Cell(60,8,'info@qazi.com',0,1,'L',0);

        //ligne 3
        $pdf->SetY(56);
        $pdf->SetX(162);
        $pdf->Cell(60,8,'+243995053623',0,1,'L',0);

        //tracer une ligne horizontale
        $pdf->Line(15, 70, 195, 70);

        //Inclure les infos du client
        $pdf->SetY(75);
        $pdf->SetX(15);
        $pdf->Cell(60,8,'Info du Client :',0,1,'L',0);
        //nom du client
        $pdf->SetY(80);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getNom(),0,1,'L',0);
        //son adresse physique
        $pdf->SetY(85);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getAdressephysique(),0,1,'L',0);
        //son email
        $pdf->SetY(90);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getEmail(),0,1,'L',0);
        //son telephone
        $pdf->SetY(95);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getTelephone(),0,1,'L',0);

        //Infos de la facture
        //label numero facture
        $pdf->SetY(75);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Facture No',0,1,'L',0);

        //label date debut
        $pdf->SetY(80);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Date d\'etablissement',0,1,'L',0);
        //label date d'expiration
        $pdf->SetY(85);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Date d\'expiration',0,1,'L',0);
        //valeur numero facture
        $pdf->SetY(75);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getNumeroFacture(),0,1,'L',0);

        //valeur date etablissement
        $pdf->SetY(80);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getDateDebut()->format('d-m-Y'),0,1,'L',0);

        //valeur date expiration
        $pdf->SetY(85);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$facture->getDateFin()->format('d-m-Y'),0,1,'L',0);

        //tableau des ligne de la facture
        $tabY = 105;
        $prodX = 15; $qtyX = 57; $puX = 99; $totX=143;
        // 1° ligne du tableau


        $pdf->SetY($tabY);

        $pdf->SetX($prodX);
        $pdf->Cell(50,8,ucfirst($facture->getTypeBien()),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
        // position de la colonne 2 (70 = 10+60)
        $pdf->SetX($qtyX);
        $pdf->Cell(50,8,'Quantite',1,0,'C',1);
        // position de la colonne 3 (130 = 70+60)
        $pdf->SetX($puX);
        $pdf->Cell(50,8,'Prix Unitaire',1,0,'C',1);

        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Total',1,0,'C',1);
        $grandTotal = 0;
        foreach($lignes as $ligne){
            $tabY = $tabY +8;
            $pdf->SetY($tabY);

            $pdf->SetX($prodX);
            //$pdf->Cell(50,8,$ligne->getProduit()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
            if($facture->getTypeBien() =='produit'){
                $pdf->Cell(50,8,$ligne->getProduit()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne

            }else{
                $pdf->Cell(50,8,$ligne->getService()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
            }

            // position de la colonne 2 (70 = 10+60)
            $pdf->SetX($qtyX);
            $pdf->Cell(50,8,$ligne->getQuantite(),1,0,'C',1);
            // position de la colonne 3 (130 = 70+60)
            $pdf->SetX($puX);
            $pdf->Cell(50,8,$ligne->getPrix(),1,0,'C',1);
            $total = $ligne->getQuantite() * $ligne->getPrix();
            $grandTotal = $grandTotal+$total;
            $pdf->SetX($totX);
            $pdf->Cell(50,8,$total,1,0,'C',1);
        }

        $tabY = $tabY +15;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Grand Total: '.$grandTotal,0,1,'R',0);

        //Visualiser dans le navigateur
        $eol = PHP_EOL;
        // email stuff (change data below)
        $mailContent = 'Votre Facture\n';
        $mailContent .= 'Cher client, veuillez trouver en attache votre facture \n';
        $mailContent .= 'Merci de nous faire confiance ';

        //return $pdf->Output('S');
        ///comment the below code to readjust the pdf file

        $attachment = $pdf->Output('attachment.pdf', 'S');
        $mail->AddStringAttachment($attachment, 'attachment.pdf');

        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Host = 'smtp.hostinger.com';
        $mail->Port = '465';
        $mail->isHTML(true);
        $mail->Username = 'admin@insoftware.tech';
        $mail->Password = 'Insoft@123';
        $mail->SetFrom('admin@insoftware.tech');
        //$mail->addAttachment($myFile);

        $mail->Subject = ucfirst('Votre facture est prete');
        $mail->Body = $mailContent;
        $mail->AddAddress($facture->getClient()->getEmail());
        return $mail->Send();


        //return $this->senderEmail('Test d\'envoi de facture',$mailContent,'gabykatonge@isicom.education');
        //return $pdf->Output('S');

//        if(mail($to, $subject, $body, $headers)){
//            $_SESSION['send_ok'] = true;
//            header("Location: gererorg.php");
//        }else{
//            header("Location: historique.php");
//        }

        // Télécharger le PDF
        /*
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="votre_fichier.pdf"',
        ]);
        */
    }



    public function generateCommandeApproPdf(array $produits, string $createdBy): string
    {
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetMargins(15, 10, 15);

        // En-tête
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetFillColor(67, 97, 238); // primary blue
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 12, 'BON DE COMMANDE - APPROVISIONNEMENT', 0, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Ln(3);
        $pdf->Cell(0, 6, 'Date : ' . date('d/m/Y'), 0, 1, 'R');
        $pdf->Cell(0, 6, 'Etabli par : ' . $createdBy, 0, 1, 'R');
        $pdf->Ln(3);

        // En-tête du tableau
        $pdf->SetFillColor(230, 234, 255);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(15, 8, 'N°', 1, 0, 'C', true);
        $pdf->Cell(110, 8, 'Désignation du produit', 1, 0, 'C', true);
        $pdf->Cell(35, 8, 'Qté à commander', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'Reçu', 1, 1, 'C', true);

        // Lignes
        $pdf->SetFont('Arial', '', 9);
        $fill = false;
        $i = 1;
        foreach ($produits as $p) {
            $pdf->SetFillColor(245, 246, 255);
            $pdf->Cell(15, 7, $i++, 1, 0, 'C', $fill);
            $pdf->Cell(110, 7, iconv('UTF-8', 'windows-1252//TRANSLIT', $p['designation']), 1, 0, 'L', $fill);
            $pdf->Cell(35, 7, number_format((float)$p['quantite'], 0, ',', ' '), 1, 0, 'C', $fill);
            $pdf->Cell(20, 7, '', 1, 1, 'C', $fill);
            $fill = !$fill;
        }

        // Pied de page
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 6, 'Signature : ___________________________', 0, 1, 'R');

        return $pdf->Output('S');
    }

    public function generateBonPdf(BonProduit $bonProduit,
                                    FactureRepository $factureRepository,
                                    LigneBonProduitRepository $ligneBonProduitRepository)
    {
        $facture = $factureRepository->find($bonProduit->getFacture()->getId());
        $lignesBon = $ligneBonProduitRepository->findBy(['bonProduit'=>$bonProduit->getId()]);
        $pdf = $this->fPDF;
        $mail = new PHPMailer();
        //$pdf->AddPage();

        $pdf->SetDrawColor(183); // Couleur du fond RVB
        $pdf->SetFillColor(221); // Couleur des filets RVB
        $pdf->SetTextColor(0); // Couleur du texte noir

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetY(20);
        $pdf->SetX(15);
        $pdf->Cell(40, 10, 'Facture No '.$facture->getNumeroFacture());

        $pdf->SetY(28);
        $pdf->SetX(15);
        $pdf->Cell(40, 10, 'Bon No '. $bonProduit->getNumero());

        $pdf->Image('assets/images/qazi.png', 147, 0, 50);

        // informations sur qazi
        // ligne 1
        $pdf->SetY(40);
        $pdf->SetX(90);
        $pdf->Cell(60,8,'13 Tetrick Road, Cypress Gardens, Florida, 33884, US',0,1,'L',0);

        // ligne 2
        $pdf->SetY(48);
        $pdf->SetX(165);
        $pdf->Cell(60,8,'info@qazi.com',0,1,'L',0);

        //ligne 3
        $pdf->SetY(56);
        $pdf->SetX(162);
        $pdf->Cell(60,8,'+243995053623',0,1,'L',0);

        //tracer une ligne horizontale
        $pdf->Line(15, 70, 195, 70);

        //Inclure les infos du client
        $pdf->SetY(75);
        $pdf->SetX(15);
        $pdf->Cell(60,8,'Info du Client :',0,1,'L',0);
        //nom du client
        $pdf->SetY(80);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getNom(),0,1,'L',0);
        //son adresse physique
        $pdf->SetY(85);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getAdressephysique(),0,1,'L',0);
        //son email
        $pdf->SetY(90);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getEmail(),0,1,'L',0);
        //son telephone
        $pdf->SetY(95);
        $pdf->SetX(15);
        $pdf->Cell(60,8,$facture->getClient()->getTelephone(),0,1,'L',0);

        //Infos du bon
        //label numero facture
        $pdf->SetY(75);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Bon No',0,1,'L',0);

        //label date debut
        $pdf->SetY(80);
        $pdf->SetX(110);
        $pdf->Cell(60,8,'Date d\'etablissement',0,1,'L',0);

        //valeur numero bon
        $pdf->SetY(75);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$bonProduit->getNumero(),0,1,'L',0);

        //valeur date etablissement
        $pdf->SetY(80);
        $pdf->SetX(170);
        $pdf->Cell(60,8,': '.$bonProduit->getCreatedAt()->format('d-m-Y'),0,1,'L',0);


        //tableau des ligne de la facture
        $tabY = 105;
        $prodX = 15; $qtyX = 57; $puX = 99; $totX=143;
        // 1° ligne du tableau


        $pdf->SetY($tabY);

        $pdf->SetX($prodX);
        $pdf->Cell(50,8,ucfirst($facture->getTypeBien()) ,1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
        // position de la colonne 2 (70 = 10+60)
        $pdf->SetX($qtyX);
        $pdf->Cell(50,8,'Quantite',1,0,'C',1);
        // position de la colonne 3 (130 = 70+60)
        $pdf->SetX($puX);
        $pdf->Cell(50,8,'Prix Unitaire',1,0,'C',1);

        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Total',1,0,'C',1);
        $grandTotal = 0;
        foreach($lignesBon as $ligne){
            $tabY = $tabY +8;
            $pdf->SetY($tabY);

            $pdf->SetX($prodX);
            if($facture->getTypeBien() =='produit'){
                $pdf->Cell(50,8,$ligne->getLigneFacture()->getProduit()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne

            }else{
                $pdf->Cell(50,8,$ligne->getLigneFacture()->getService()->getDesignation(),1,0,'C',1);  // 60 >largeur colonne, 8 >hauteur colonne
            }

            // position de la colonne 2 (70 = 10+60)
            $pdf->SetX($qtyX);
            $pdf->Cell(50,8,$ligne->getLigneFacture()->getQuantite(),1,0,'C',1);
            // position de la colonne 3 (130 = 70+60)
            $pdf->SetX($puX);
            $pdf->Cell(50,8,$ligne->getLigneFacture()->getPrix(),1,0,'C',1);
            $total = $ligne->getLigneFacture()->getQuantite() * $ligne->getLigneFacture()->getPrix();
            $grandTotal = $grandTotal+$total;
            $pdf->SetX($totX);
            $pdf->Cell(50,8,$total,1,0,'C',1);
        }
        //Sous-total
        $tabY = $tabY +15;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Sous-Total: '.$grandTotal,0,1,'R',0);

        //Sous-total
        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'TVA: '.$bonProduit->getFacture()->getTax().'%',0,1,'R',0);

//Sous-total
        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Reduction: '.$bonProduit->getFacture()->getDiscount().'%',0,1,'R',0);

        $taxable = $grandTotal + (($grandTotal * $bonProduit->getFacture()->getTax())/100);
        $reductible = ($grandTotal * $bonProduit->getFacture()->getDiscount())/100;
        $total = $taxable - $reductible;

        $tabY = $tabY +8;
        $pdf->SetY($tabY);
        $pdf->SetX($totX);
        $pdf->Cell(50,8,'Grand-Total: '.$total,0,1,'R',0);

        //Visualiser dans le navigateur


        return $pdf->Output('S');
    }


}
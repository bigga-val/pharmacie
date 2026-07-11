<?php

namespace App\Service;

use Dompdf\Adapter\PDFLib;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private $domPDF;

    public function  __construct()
    {
        $this->domPDF = new DomPDF();
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Garamond');
        $this->domPDF->setOptions($pdfOptions);

    }

    public function showPdfFile($html, string $filename = 'document.pdf'): void
    {
        $this->domPDF->loadHtml($html);
        $this->domPDF->render();
        $this->domPDF->stream($filename, ['Attachment' => false]);
    }

    public function downloadPdfFile($html, string $filename = 'document.pdf'): void
    {
        $this->domPDF->loadHtml($html);
        $this->domPDF->render();
        $this->domPDF->stream($filename, ['Attachment' => true]);
    }
}
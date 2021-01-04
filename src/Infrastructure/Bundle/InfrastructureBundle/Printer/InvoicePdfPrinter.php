<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer;

use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class InvoicePdfPrinter
{
    /** @var RouterInterface */
    private $router;

    /** @var PdfPrinter */
    private $pdfPrinter;

    public function __construct(RouterInterface $router, PdfPrinter $pdfPrinter)
    {
        $this->router = $router;
        $this->pdfPrinter = $pdfPrinter;
    }

    public function generate(Invoice $invoice): string
    {
        $pathToPdf = sprintf(
            '%s/invoice-%s.pdf',
            sys_get_temp_dir(),
            $invoice->getId()
        );

        $urlToPrint = $this->router->generate(
            'event_invoice_show',
            [
                'sheet'   => $invoice->getSheet()->getId(),
                'invoice' => $invoice->getId(),
                'hash'    => $invoice->getHash(),
                'format'  => 'html',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->pdfPrinter->generate($urlToPrint, $pathToPdf);
    }
}

<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer;

use Proximum\Vimeet\Application\Adapter\InvoicesPdfBulkPrinterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class InvoicesPdfBulkPrinter implements InvoicesPdfBulkPrinterInterface
{
    /** @var RouterInterface */
    private $router;

    /** @var PdfPrinter */
    private $pdfPrinter;

    /** @var string */
    private $printInvoicesPath;

    public function __construct(RouterInterface $router, PdfPrinter $pdfPrinter, string $printInvoicesPath)
    {
        $this->router = $router;
        $this->pdfPrinter = $pdfPrinter;
        $this->printInvoicesPath = $printInvoicesPath;
    }

    /**
     * @param int[] $sheetIds
     *
     * @return string
     */
    public function generate(array $sheetIds): string
    {
        $pathToPdf = sprintf('%s/invoices-%s.pdf', $this->printInvoicesPath, sha1(implode('-', $sheetIds)));

        $urlToPrint = $this->router->generate(
            'admin_invoice_show_bulk',
            [
                'sheetIds' => $sheetIds,
                'format'   => 'html',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->pdfPrinter->generate($urlToPrint, $pathToPdf);
    }
}

<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Printer;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer\InvoicesPdfBulkPrinter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer\PdfPrinter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class InvoicesPdfBulkPrinterTest extends TestCase
{
    public function testGenerate(): void
    {
        // prepare data
        $sheetIds = [42, 314];

        // prophecy dependencies
        $router = $this->prophesize(RouterInterface::class);
        $pdfPrinter = $this->prophesize(PdfPrinter::class);

        $router->generate(
            'admin_invoice_show_bulk',
            [
                'sheetIds' => $sheetIds,
                'format'   => 'html',
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        )
            ->shouldBeCalled()
            ->willReturn('http://website/invoices.html')
        ;

        $pdfPrinter->generate('http://website/invoices.html', '/foo/invoices-'.sha1(implode('-', $sheetIds)).'.pdf');

        // run tests;
        $invoicesPdfBulkPrinter = new InvoicesPdfBulkPrinter($router->reveal(), $pdfPrinter->reveal(), '/foo');
        $invoicesPdfBulkPrinter->generate($sheetIds);
    }
}

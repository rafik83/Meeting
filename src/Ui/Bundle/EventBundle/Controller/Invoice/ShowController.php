<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Invoice;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceQuery;
use Proximum\Vimeet\Application\View\Invoice\InvoiceView;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer\InvoicePdfPrinter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ShowController extends AbstractController
{
    private InvoicePdfPrinter $invoicePdfPrinter;
    private QueryBusInterface $queryBus;

    public function __construct(
        InvoicePdfPrinter $invoicePdfPrinter,
        QueryBusInterface $queryBus
    ) {
        $this->invoicePdfPrinter = $invoicePdfPrinter;
        $this->queryBus = $queryBus;
    }

    /**
     * This controller is public
     * Do not add SheetVoter
     *
     * @param string $format 'html'|'pdf'
     */
    public function showAction(EventDomain $eventDomain, Sheet $sheet, Invoice $invoice, string $hash, string $format): Response
    {
        if ($invoice->getSheet() !== $sheet || $invoice->getHash() !== $hash) {
            throw $this->createNotFoundException();
        }

        if ('pdf' === $format) {
            return new BinaryFileResponse(
                $this->invoicePdfPrinter->generate($invoice)
            );
        }

        /** @var InvoiceView $invoiceView */
        $invoiceView = $this->queryBus->handle(new InvoiceQuery($invoice));

        return $this->render(
            'EventBundle:Invoice:show.html.twig',
            [
                'invoiceView' => $invoiceView,
            ]
        );
    }
}

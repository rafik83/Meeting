<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Invoice;

use Proximum\Vimeet\Application\Query\Invoice\InvoiceQuery;
use Proximum\Vimeet\Application\View\Invoice\InvoiceView;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ShowController extends Controller
{
    /**
     * This controller is public
     * Do not add SheetVoter
     *
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Invoice     $invoice
     * @param string      $hash
     * @param string      $format      'html'|'pdf'
     *
     * @return Response
     */
    public function showAction(EventDomain $eventDomain, Sheet $sheet, Invoice $invoice, $hash, $format)
    {
        if ($invoice->getSheet() !== $sheet || $invoice->getHash() !== $hash) {
            throw $this->createNotFoundException();
        }

        if ('pdf' === $format) {
            return new BinaryFileResponse(
                $this->get('printer.invoice_pdf_printer')->generate($invoice)
            );
        }

        /** @var InvoiceView $invoiceView */
        $invoiceView = $this->get('tactician.commandbus')->handle(new InvoiceQuery($invoice));

        return $this->render(
            'EventBundle:Invoice:show.html.twig',
            [
                'invoiceView' => $invoiceView,
            ]
        );
    }
}

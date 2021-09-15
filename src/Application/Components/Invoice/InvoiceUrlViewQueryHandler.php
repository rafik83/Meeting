<?php

namespace Proximum\Vimeet\Application\Components\Invoice;

use Proximum\Vimeet\Application\View\Invoice\InvoiceUrlView;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;

class InvoiceUrlViewQueryHandler
{
    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /**
     * @param EventUrlGeneratorInterface $eventUrlGenerator
     */
    public function __construct(EventUrlGeneratorInterface $eventUrlGenerator)
    {
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    /**
     * @param InvoiceUrlViewQuery $invoiceUrlViewQuery
     *
     * @return InvoiceUrlView
     */
    public function handle(InvoiceUrlViewQuery $invoiceUrlViewQuery)
    {
        $invoice = $invoiceUrlViewQuery->invoice;

        $url = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $invoice->getEvent(),
            'event_invoice_show',
            [
                'sheet'   => $invoice->getSheet()->getId(),
                'invoice' => $invoice->getId(),
                'hash'    => $invoice->getHash(),
                'format'  => 'pdf',
                '_locale' => $invoice->getEvent()->getAvailableLocale($invoice->getSheet()->getOwnerLocale()),
            ]
        );

        return new InvoiceUrlView(
            $invoice->getId(),
            $invoice->getNumber(),
            $url
        );
    }
}

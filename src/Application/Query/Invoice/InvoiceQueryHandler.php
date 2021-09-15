<?php

namespace Proximum\Vimeet\Application\Query\Invoice;

use Proximum\Vimeet\Application\Query\Invoice\BillingInfos\BillingInfosQuery;
use Proximum\Vimeet\Application\Query\Invoice\BillingInfos\BillingInfosQueryHandler;
use Proximum\Vimeet\Application\View\Invoice\InvoiceView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\SerializerAdapter;

class InvoiceQueryHandler
{
    /**
     * @var SerializerAdapter
     */
    private $serializer;

    /** @var BillingInfosQueryHandler */
    private $billingInfosQueryHandler;

    /**
     * @param SerializerAdapter        $serializer
     * @param BillingInfosQueryHandler $billingInfoViewQueryHandler
     */
    public function __construct(
        SerializerAdapter $serializer,
        BillingInfosQueryHandler $billingInfoViewQueryHandler
    ) {
        $this->serializer               = $serializer;
        $this->billingInfosQueryHandler = $billingInfoViewQueryHandler;
    }

    /**
     * @param InvoiceQuery $invoiceQuery
     *
     * @return InvoiceView
     */
    public function handle(InvoiceQuery $invoiceQuery)
    {
        // Retrieve the billingInfo from the sheet
        $billingInfoViewOfSheet = $this->billingInfosQueryHandler->handle(
            new BillingInfosQuery($invoiceQuery->invoice->getSheet())
        );

        return $this->serializer->deserialize(
            $invoiceQuery->invoice->getData(),
            InvoiceView::class,
            'json',
            ['invoice' => $invoiceQuery->invoice, 'billingInfosViewOfSheet' => $billingInfoViewOfSheet]
        );
    }
}

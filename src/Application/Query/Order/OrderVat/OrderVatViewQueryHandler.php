<?php

namespace Proximum\Vimeet\Application\Query\Order\OrderVat;

use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Domain\View\OrderVatView;

class OrderVatViewQueryHandler
{
    /** @var VatApplicable */
    private $vatApplicable;

    /** @var VatListViewQueryHandler */
    private $vatListViewQueryHandler;

    /**
     * @param VatApplicable           $vatApplicable
     * @param VatListViewQueryHandler $vatListViewQueryHandler
     */
    public function __construct(
        VatApplicable $vatApplicable,
        VatListViewQueryHandler $vatListViewQueryHandler
    ) {
        $this->vatApplicable = $vatApplicable;
        $this->vatListViewQueryHandler = $vatListViewQueryHandler;
    }

    /**
     * @param OrderVatViewQuery $orderVatViewQuery
     *
     * @return OrderVatView
     *
     * @throws MissingBillingInfoException
     */
    public function handle(OrderVatViewQuery $orderVatViewQuery): OrderVatView
    {
        $order = $orderVatViewQuery->order;

        $isVatApplicable = $this->vatApplicable->onSheet($order->getSheet());
        $vatListView = $this->vatListViewQueryHandler->handle(new VatListViewQuery($order, $isVatApplicable));

        return new OrderVatView(
            $order->getNumero(),
            $order->getId(),
            $order->getSheet()->getId(),
            $isVatApplicable,
            $order->getVatRate(),
            $order->getVatMode(),
            $order->getCurrency(),
            $order->isCancelled(),
            $vatListView->total,
            $vatListView->getVatAmount(),
            $vatListView->totalWithVat,
            $vatListView,
            $order->getCreatedAt(),
            $order->getInvoice()
        );
    }
}

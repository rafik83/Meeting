<?php

namespace Proximum\Vimeet\Application\Query\Order\OrderVat;

use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\OrderVatView;

class OrderVatViewsByEventQueryHandler
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var OrderVatViewQueryHandler */
    private $orderVatViewQueryHandler;

    /** @var BillingInfoRepositoryInterface */
    private $billingInfoRepository;

    public function __construct(
        BillingInfoRepositoryInterface $billingInfoRepository,
        OrderRepositoryInterface $orderRepository,
        OrderVatViewQueryHandler $orderVatViewQueryHandler
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderVatViewQueryHandler = $orderVatViewQueryHandler;
        $this->billingInfoRepository = $billingInfoRepository;
    }

    /**
     * @param OrderVatViewsByEventQuery $orderVatViewsByEventQuery
     *
     * @throws MissingBillingInfoException
     *
     * @return OrderVatView[]
     */
    public function handle(OrderVatViewsByEventQuery $orderVatViewsByEventQuery)
    {
        $orders = $this->orderRepository->findByEventAndEnabledSheets($orderVatViewsByEventQuery->event);

        $sheets = [];
        foreach ($orders as $order) {
            $sheets[$order->getSheet()->getId()] = $order->getSheet();
        }

        $this->billingInfoRepository->loadBySheets($sheets);

        $orderVatViews = [];

        foreach ($orders as $order) {
            $orderVatViews[] = $this->orderVatViewQueryHandler->handle(new OrderVatViewQuery($order));
        }

        return $orderVatViews;
    }
}

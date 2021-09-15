<?php

namespace Proximum\Vimeet\Application\Query\Order\OrderVat;

use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\OrderVatView;

class OrderVatViewsBySheetQueryHandler
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var OrderVatViewQueryHandler */
    private $orderVatViewQueryHandler;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderVatViewQueryHandler $orderVatViewQueryHandler
     */
    public function __construct(OrderRepositoryInterface $orderRepository, OrderVatViewQueryHandler $orderVatViewQueryHandler)
    {
        $this->orderRepository = $orderRepository;
        $this->orderVatViewQueryHandler = $orderVatViewQueryHandler;
    }

    /**
     * @param OrderVatViewsBySheetQuery $orderVatViewsBySheetQuery
     *
     * @throws MissingBillingInfoException
     *
     * @return OrderVatView[]
     */
    public function handle(OrderVatViewsBySheetQuery $orderVatViewsBySheetQuery)
    {
        $orders = $this->orderRepository->findBySheet($orderVatViewsBySheetQuery->sheet);

        $orderVatViews = [];

        foreach ($orders as $order) {
            $orderVatViews[] = $this->orderVatViewQueryHandler->handle(new OrderVatViewQuery($order));
        }

        return $orderVatViews;
    }
}

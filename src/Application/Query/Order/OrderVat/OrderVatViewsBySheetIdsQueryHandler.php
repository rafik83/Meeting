<?php

namespace Proximum\Vimeet\Application\Query\Order\OrderVat;

use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\OrderVatView;

class OrderVatViewsBySheetIdsQueryHandler
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var OrderVatViewQueryHandler */
    private $orderVatViewQueryHandler;

    /** @var BillingInfoRepositoryInterface */
    private $billingInfoRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderVatViewQueryHandler $orderVatViewQueryHandler,
        BillingInfoRepositoryInterface $billingInfoRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderVatViewQueryHandler = $orderVatViewQueryHandler;
        $this->billingInfoRepository = $billingInfoRepository;
    }

    /**
     * @param OrderVatViewsBySheetIdsQuery $orderVatViewsBySheetIdsQuery
     *
     * @throws MissingBillingInfoException
     *
     * @return OrderVatView[]
     */
    public function handle(OrderVatViewsBySheetIdsQuery $orderVatViewsBySheetIdsQuery): array
    {
        $orders = $this->orderRepository->findByEventAndSheetIds(
            $orderVatViewsBySheetIdsQuery->event,
            $orderVatViewsBySheetIdsQuery->sheetIds
        );

        $this->billingInfoRepository->loadBySheets($orderVatViewsBySheetIdsQuery->sheetIds);

        $orderVatViews = [];

        foreach ($orders as $order) {
            $orderVatViews[] = $this->orderVatViewQueryHandler->handle(new OrderVatViewQuery($order));
        }

        return $orderVatViews;
    }
}

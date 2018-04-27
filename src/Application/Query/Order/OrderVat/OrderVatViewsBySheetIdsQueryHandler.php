<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\OrderVat;

use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\OrderVatView;

class OrderVatViewsBySheetIdsQueryHandler
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
     * @param OrderVatViewsBySheetIdsQuery $orderVatViewsBySheetIdsQuery
     *
     * @throws MissingBillingInfoException
     *
     * @return OrderVatView[]
     */
    public function handle(OrderVatViewsBySheetIdsQuery $orderVatViewsBySheetIdsQuery)
    {
        $orders = $this->orderRepository->findByEventAndSheetIds(
            $orderVatViewsBySheetIdsQuery->event,
            $orderVatViewsBySheetIdsQuery->sheetIds
        );

        $orderVatViews = [];

        foreach ($orders as $order) {
            $orderVatViews[] = $this->orderVatViewQueryHandler->handle(new OrderVatViewQuery($order));
        }

        return $orderVatViews;
    }
}

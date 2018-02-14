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

class OrderVatViewsByEventQueryHandler
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
     * @param OrderVatViewsByEventQuery $orderVatViewsByEventQuery
     *
     * @return OrderVatView[]
     *
     * @throws MissingBillingInfoException
     */
    public function handle(OrderVatViewsByEventQuery $orderVatViewsByEventQuery)
    {
        $orders = $this->orderRepository->findByEventAndEnabledSheets($orderVatViewsByEventQuery->event);

        $orderVatViews = [];

        foreach ($orders as $order) {
            $orderVatViews[] = $this->orderVatViewQueryHandler->handle(new OrderVatViewQuery($order));
        }

        return $orderVatViews;
    }
}

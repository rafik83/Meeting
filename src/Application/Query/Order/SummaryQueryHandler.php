<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order;

use Proximum\Vimeet\Application\Query\Order\Summary\GroupsViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Summary\GroupsViewQuery;
use Proximum\Vimeet\Application\Query\Order\Summary\PromotionCodesViewQuery;
use Proximum\Vimeet\Application\Query\Order\Summary\PromotionCodesViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\SummaryView;
use Proximum\Vimeet\Domain\Order\Balance;

class SummaryQueryHandler
{
    /**
     * @var GroupsViewQueryHandler
     */
    private $groupsViewQueryHandler;

    /**
     * @var PromotionCodesViewQueryHandler
     */
    private $promotionCodesViewQueryHandler;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * @param GroupsViewQueryHandler         $groupsViewQueryHandler
     * @param PromotionCodesViewQueryHandler $promotionCodesViewQueryHandler
     * @param Balance                        $balance
     */
    public function __construct(
        GroupsViewQueryHandler $groupsViewQueryHandler,
        PromotionCodesViewQueryHandler $promotionCodesViewQueryHandler,
        Balance $balance
    ) {
        $this->groupsViewQueryHandler         = $groupsViewQueryHandler;
        $this->promotionCodesViewQueryHandler = $promotionCodesViewQueryHandler;
        $this->balance                        = $balance;
    }
    /**
     * @param SummaryQuery $summaryQuery
     *
     * @return SummaryView
     */
    public function handle(SummaryQuery $summaryQuery)
    {
        return new SummaryView(
            $this->groupsViewQueryHandler->handle(
                new GroupsViewQuery(
                    $summaryQuery->sheet,
                    $summaryQuery->order,
                    $summaryQuery->locale
                )
            ),
            $this->promotionCodesViewQueryHandler->handle(
                new PromotionCodesViewQuery(
                    $summaryQuery->order,
                    $summaryQuery->locale
                )
            ),
            $summaryQuery->order->isVatApplicable(),
            $summaryQuery->order->getVatRate(),
            $summaryQuery->order->getVatAmount(),
            $summaryQuery->order->getVatMode(),
            $summaryQuery->order->getTotalVatMode(),
            $summaryQuery->order->getTotalWithoutVat(),
            $summaryQuery->order->getTotalWithVat(),
            $summaryQuery->order->getCurrency(),
            $this->balance->getRemainingToPay($summaryQuery->sheet),
            $summaryQuery->sheet
        );
    }
}

<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order;

use Proximum\Vimeet\Application\Query\Order\Summary\CustomRowsViewQuery;
use Proximum\Vimeet\Application\Query\Order\Summary\CustomRowsViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Summary\GroupsViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\Summary\GroupsViewQuery;
use Proximum\Vimeet\Application\Query\Order\Summary\PromotionCodesViewQuery;
use Proximum\Vimeet\Application\Query\Order\Summary\PromotionCodesViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\SummaryView;

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
     * @var CustomRowsViewQueryHandler
     */
    private $customRowsViewQueryHandler;

    /**
     * @param GroupsViewQueryHandler         $groupsViewQueryHandler
     * @param PromotionCodesViewQueryHandler $promotionCodesViewQueryHandler
     * @param CustomRowsViewQueryHandler     $customRowsViewQueryHandler
     */
    public function __construct(
        GroupsViewQueryHandler $groupsViewQueryHandler,
        PromotionCodesViewQueryHandler $promotionCodesViewQueryHandler,
        CustomRowsViewQueryHandler $customRowsViewQueryHandler
    ) {
        $this->groupsViewQueryHandler         = $groupsViewQueryHandler;
        $this->promotionCodesViewQueryHandler = $promotionCodesViewQueryHandler;
        $this->customRowsViewQueryHandler     = $customRowsViewQueryHandler;
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
            $summaryQuery->order->getVatMode(),
            $summaryQuery->order->getCurrency()
        );
    }
}

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
use Proximum\Vimeet\Application\View\Order\SummaryView;

class SummaryQueryHandler
{
    /**
     * @var GroupsViewQueryHandler
     */
    private $groupsViewQueryHandler;

    /**
     * @param GroupsViewQueryHandler $groupsViewQueryHandler
     */
    public function __construct(GroupsViewQueryHandler $groupsViewQueryHandler)
    {
        $this->groupsViewQueryHandler = $groupsViewQueryHandler;
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
            $summaryQuery->order->isVatApplicable(),
            $summaryQuery->order->getVatRate(),
            $summaryQuery->order->getVatMode(),
            $summaryQuery->order->getCurrency()
        );
    }
}

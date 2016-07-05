<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\SummaryView;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;

class SummaryViewQueryHandler
{
    /**
     * @var GroupsViewQueryHandler
     */
    public $groupsViewQueryHandler;

    /**
     * @var VatApplicable
     */
    public $vatApplicable;

    /**
     * @param GroupsViewQueryHandler $groupsViewQueryHandler
     * @param VatApplicable          $vatApplicable
     */
    public function __construct(GroupsViewQueryHandler $groupsViewQueryHandler, VatApplicable $vatApplicable)
    {
        $this->groupsViewQueryHandler = $groupsViewQueryHandler;
        $this->vatApplicable          = $vatApplicable;
    }

    /**
     * @param SummaryViewQuery $summaryViewQuery
     *
     * @return SummaryView
     */
    public function handle(SummaryViewQuery $summaryViewQuery)
    {
        $groups = $this->groupsViewQueryHandler->handle(new GroupsViewQuery(
            $summaryViewQuery->sheet,
            $summaryViewQuery->cart,
            $summaryViewQuery->locale
        ));

        return new SummaryView(
            $summaryViewQuery->funnel,
            $groups,
            $summaryViewQuery->sheet->getEvent()->getMode(),
            $summaryViewQuery->sheet->getEvent()->getVat(),
            $groups->getTotal(),
            $summaryViewQuery->sheet->getEvent()->getCurrency(),
            $this->vatApplicable->onCart($summaryViewQuery->cart)
        );
    }
}

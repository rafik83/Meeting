<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\SummaryView;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;

class SummaryViewQueryHandler
{
    /** @var GroupsViewQueryHandler */
    public $groupsViewQueryHandler;

    /** @var VatApplicable */
    public $vatApplicable;

    /** @var PromotionCodeQueryHandler */
    public $promotionCodeQueryHandler;

    /**
     * @param GroupsViewQueryHandler    $groupsViewQueryHandler
     * @param VatApplicable             $vatApplicable
     * @param PromotionCodeQueryHandler $promotionCodeQueryHandler
     */
    public function __construct(
        GroupsViewQueryHandler $groupsViewQueryHandler,
        VatApplicable $vatApplicable,
        PromotionCodeQueryHandler $promotionCodeQueryHandler
    ) {
        $this->groupsViewQueryHandler    = $groupsViewQueryHandler;
        $this->vatApplicable             = $vatApplicable;
        $this->promotionCodeQueryHandler = $promotionCodeQueryHandler;
    }

    /**
     * @param SummaryViewQuery $summaryViewQuery
     *
     * @return SummaryView
     */
    public function handle(SummaryViewQuery $summaryViewQuery): SummaryView
    {
        $groups = $this->groupsViewQueryHandler->handle(new GroupsViewQuery(
            $summaryViewQuery->sheet,
            $summaryViewQuery->cart,
            $summaryViewQuery->locale
        ));

        $promoCodes = $this->promotionCodeQueryHandler->handle(new PromotionCodeQuery(
            $summaryViewQuery->sheet,
            $summaryViewQuery->cart,
            $summaryViewQuery->locale
        ));

        $total = $groups->getTotal() + $promoCodes->getTotal();

        return new SummaryView(
            $summaryViewQuery->funnel,
            $groups,
            $promoCodes,
            $summaryViewQuery->sheet->getEvent()->getMode(),
            $summaryViewQuery->sheet->getEvent()->getVat(),
            $total,
            $summaryViewQuery->sheet->getEvent()->getCurrency(),
            $this->vatApplicable->onSheet($summaryViewQuery->sheet)
        );
    }
}

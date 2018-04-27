<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\Query\Package\Vat\VatListViewQuery;
use Proximum\Vimeet\Application\Query\Package\Vat\VatListViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Summary\SummaryView;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;

class SummaryViewQueryHandler
{
    /** @var GroupsViewQueryHandler */
    public $groupsViewQueryHandler;

    /** @var PromotionCodeQueryHandler */
    public $promotionCodeQueryHandler;

    /** @var VatListViewQueryHandler */
    public $vatListViewQueryHandler;

    /**
     * @param GroupsViewQueryHandler    $groupsViewQueryHandler
     * @param PromotionCodeQueryHandler $promotionCodeQueryHandler
     * @param VatListViewQueryHandler   $vatListViewQueryHandler
     */
    public function __construct(
        GroupsViewQueryHandler $groupsViewQueryHandler,
        PromotionCodeQueryHandler $promotionCodeQueryHandler,
        VatListViewQueryHandler $vatListViewQueryHandler
    ) {
        $this->groupsViewQueryHandler    = $groupsViewQueryHandler;
        $this->promotionCodeQueryHandler = $promotionCodeQueryHandler;
        $this->vatListViewQueryHandler = $vatListViewQueryHandler;
    }

    /**
     * @param SummaryViewQuery $summaryViewQuery
     *
     * @throws MissingBillingInfoException
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

        $vatListView = $this->vatListViewQueryHandler->handle(
            new VatListViewQuery($summaryViewQuery->sheet, $groups, $promoCodes)
        );

        return new SummaryView(
            $summaryViewQuery->funnel,
            $groups,
            $promoCodes,
            $summaryViewQuery->sheet->getEvent()->getMode(),
            $total,
            $vatListView->totalWithVat,
            $summaryViewQuery->sheet->getEvent()->getCurrency(),
            $vatListView->vatApplicable,
            $vatListView
        );
    }
}

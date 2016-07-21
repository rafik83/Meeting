<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class BillingViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * BillingViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(NavigationBuilderInterface $navigationBuilder)
    {
        $this->navigationBuilder = $navigationBuilder;
    }

    /**
     * @param BillingViewQuery $billingQuery
     *
     * @return null|CategoryView
     */
    public function handle(BillingViewQuery $billingQuery)
    {
        if (!$billingQuery->sheet->getPackage()->isPassable()) {
            return null;
        }

        $linksView   = [];
        $linksView[] = new LinkView(
            'navigation.links.billing.billing_info',
            $this->navigationBuilder->getRoute('event_billing_info', [
                'sheet' => $billingQuery->sheet->getId()
            ])
        );

        if (count($billingQuery->sheet->getOrders()) > 0) {
            $linksView[] = new LinkView(
                'navigation.links.billing.order_history',
                $this->navigationBuilder->getRoute('event_order_list', [
                    'sheet' => $billingQuery->sheet->getId(),
                ])
            );
        }

        return new CategoryView(Category::BILLING, Category::BILLING_ICON, $linksView);
    }
}

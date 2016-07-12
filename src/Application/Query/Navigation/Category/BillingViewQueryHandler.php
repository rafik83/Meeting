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
     * @return CategoryView
     */
    public function handle(BillingViewQuery $billingQuery)
    {
        if (!$billingQuery->sheet->getPackage()->isPassable()) {
            return null;
        }

        $linksView = [];

        if (empty($billingQuery->sheet->getOrders())) {
            $linksView[] = new LinkView(
                'navigation.links.billing.billing_info',
                $this->navigationBuilder->getRoute('event_billing_info', ['sheet' => $billingQuery->sheet->getId()])
            );
        } else {
            $linksView[] = new LinkView(
                'navigation.links.billing.billing_info',
                $this->navigationBuilder->getRoute('event_billing_info', ['sheet' => $billingQuery->sheet->getId()])
            );
            $linksView[] = new LinkView(
                'navigation.links.billing.order_history',
                ''
            );
        }

        return new CategoryView(Category::BILLING, Category::BILLING_ICON, $linksView);
    }
}

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

class BillingViewQueryHandler
{
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
                ''
            );
        } else {
            $linksView[] = new LinkView(
                'navigation.links.billing.billing_info',
                ''
            );
            $linksView[] = new LinkView(
                'navigation.links.billing.order_history',
                ''
            );
        }

        return new CategoryView(
            Category::BILLING,
            Category::BILLING_ICON,
            $linksView
        );
    }
}

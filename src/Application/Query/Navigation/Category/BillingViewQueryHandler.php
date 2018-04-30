<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
        if (null === $billingQuery->sheet->getPackage() || !$billingQuery->sheet->getPackage()->isPassable()) {
            return null;
        }

        $linksView   = [];
        $linksView[] = new LinkView(
            'navigation.links.billing.billing_info',
            $this->navigationBuilder->getRoute('event_billing_info_clear_flash', [
                'sheet' => $billingQuery->sheet->getId(),
            ])
        );

        if ($billingQuery->sheet->hasOrders()) {
            $linksView[] = new LinkView(
                'navigation.links.billing.order_history',
                $this->navigationBuilder->getRoute('event_order_list', [
                    'sheet' => $billingQuery->sheet->getId(),
                ])
            );
        }

        return new CategoryView(Category::BILLING, Category::BILLING_ICON, $linksView, false);
    }
}

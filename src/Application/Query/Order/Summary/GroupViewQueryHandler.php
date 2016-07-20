<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\GroupView;
use Proximum\Vimeet\Domain\Model\Product;

class GroupViewQueryHandler
{
    /**
     * @var ProductViewQueryHandler
     */
    private $productViewQueryHandler;

    /**
     * @var CustomRowsViewQueryHandler
     */
    private $customRowViewQueryHandler;

    /**
     * @param ProductViewQueryHandler    $productViewQueryHandler
     * @param CustomRowViewQueryHandler $customRowsViewQueryHandler
     */
    public function __construct(
        ProductViewQueryHandler $productViewQueryHandler,
        CustomRowViewQueryHandler $customRowsViewQueryHandler
    ) {
        $this->productViewQueryHandler   = $productViewQueryHandler;
        $this->customRowViewQueryHandler = $customRowsViewQueryHandler;
    }

    /**
     * @param GroupViewQuery $groupViewQuery
     *
     * @return GroupView
     */
    public function handle(GroupViewQuery $groupViewQuery)
    {
        $label    = '';
        $locale   = $groupViewQuery->locale;
        $order    = $groupViewQuery->order;
        $products = [];
        $customRows = [];

        if ($groupViewQuery->type === Product::TYPE_OPTION) {
            $label = $order->getGroupLabel($groupViewQuery->groupId, $locale);

            foreach ($order->getProductRowForGroupId($groupViewQuery->groupId) as $row) {
                $products[] = $this->productViewQueryHandler->handle(
                    new ProductViewQuery(
                        $order,
                        $row,
                        $locale,
                        $groupViewQuery->planView
                    )
                );
            }

            foreach ($order->getCustomRowForGroupId($groupViewQuery->groupId) as $row) {
                $customRows[] = $this->customRowViewQueryHandler->handle(
                    new CustomRowViewQuery(
                        $row,
                        $locale
                    )
                );
            }
        } else {
            $product = $order->getProductOfType($groupViewQuery->type);

            if (null !== $product) {
                $label = $product->getLabel($locale);
                $products[] = $this->productViewQueryHandler->handle(
                    new ProductViewQuery(
                        $order,
                        $product,
                        $locale,
                        $groupViewQuery->planView
                    )
                );
            }
        }

        return new GroupView(
            $label,
            $groupViewQuery->type,
            $groupViewQuery->groupId,
            $products,
            $customRows
        );
    }
}

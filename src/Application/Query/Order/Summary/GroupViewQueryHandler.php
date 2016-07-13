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
     * @param ProductViewQueryHandler $productViewQueryHandler
     */
    public function __construct(ProductViewQueryHandler $productViewQueryHandler)
    {
        $this->productViewQueryHandler = $productViewQueryHandler;
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

        if ($groupViewQuery->type === Product::TYPE_OPTION) {
            $label = $order->getGroupLabel($groupViewQuery->groupId, $locale);

            foreach ($order->getRowForGroupId($groupViewQuery->groupId) as $row) {
                $products[] = $this->productViewQueryHandler->handle(
                    new ProductViewQuery(
                        $order,
                        $row,
                        $locale,
                        $groupViewQuery->planView
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
            $products
        );
    }
}

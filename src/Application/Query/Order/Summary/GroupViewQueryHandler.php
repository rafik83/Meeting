<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\GroupView;
use Proximum\Vimeet\Domain\Model\Product;

class GroupViewQueryHandler
{
    /**
     * @var RowViewQueryHandler
     */
    private $rowViewQueryHandler;

    /**
     * @var CustomRowViewQueryHandler
     */
    private $customRowViewQueryHandler;

    /**
     * @param RowViewQueryHandler       $rowViewQueryHandler
     * @param CustomRowViewQueryHandler $customRowViewQueryHandler
     */
    public function __construct(
        RowViewQueryHandler $rowViewQueryHandler,
        CustomRowViewQueryHandler $customRowViewQueryHandler
    ) {
        $this->rowViewQueryHandler       = $rowViewQueryHandler;
        $this->customRowViewQueryHandler = $customRowViewQueryHandler;
    }

    /**
     * @param GroupViewQuery $groupViewQuery
     *
     * @return GroupView
     */
    public function handle(GroupViewQuery $groupViewQuery)
    {
        $label      = null;
        $locale     = $groupViewQuery->locale;
        $order      = $groupViewQuery->order;
        $products   = [];
        $customRows = [];

        if (Product::TYPE_OPTION === $groupViewQuery->type) {
            $label = $order->getGroupLabel($groupViewQuery->groupId, $locale);

            foreach ($order->getProductRowsForGroupId($groupViewQuery->type, $groupViewQuery->groupId) as $row) {
                $products[] = $this->rowViewQueryHandler->handle(
                    new RowViewQuery(
                        $order,
                        $row,
                        $locale,
                        $groupViewQuery->planView
                    )
                );
            }

            foreach ($order->getCustomRowsForGroupId($groupViewQuery->groupId) as $row) {
                $customRows[] = $this->customRowViewQueryHandler->handle(
                    new CustomRowViewQuery(
                        $row,
                        $locale
                    )
                );
            }
        } else {
            foreach ($order->getRowsProductOfType($groupViewQuery->type) as $product) {
                if (null === $label) {
                    $label = $product->getLabel($locale);
                }

                $products[] = $this->rowViewQueryHandler->handle(
                    new RowViewQuery(
                        $order,
                        $product,
                        $locale,
                        $groupViewQuery->planView
                    )
                );
            }
        }

        return new GroupView(
            $groupViewQuery->sheet,
            $label,
            $groupViewQuery->type,
            $groupViewQuery->groupId,
            $products,
            $customRows,
            (isset($groupViewQuery->step)) ? $groupViewQuery->step->index : null
        );
    }
}

<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\CustomRowsView;

class CustomRowsViewQueryHandler
{
    /**
     * @var CustomRowViewQueryHandler
     */
    private $customRowViewQueryHandler;

    /**
     * @param CustomRowViewQueryHandler customRowViewQueryHandler
     */
    public function __construct(CustomRowViewQueryHandler $customRowViewQueryHandler)
    {
        $this->customRowViewQueryHandler = $customRowViewQueryHandler;
    }

    /**
     * @param CustomRowsViewQuery $customRowsViewQuery
     *
     * @return CustomRowsView
     */
    public function handle(CustomRowsViewQuery $customRowsViewQuery)
    {
        $customRowsView = new CustomRowsView();
        $locale         = $customRowsViewQuery->locale;
        $order          = $customRowsViewQuery->order;

        foreach ($order->getCustomRows() as $customRow) {
            $customRowsView->addCustomRow(
                $this->customRowViewQueryHandler->handle(
                    new CustomRowViewQuery(
                        $customRow,
                        $locale
                    )
                )
            );
        }

        return $customRowsView;
    }
}

<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Spot\Agenda;

use Proximum\Vimeet\Application\View\Spot\Agenda\SpotView;

class SpotViewQueryHandler
{
    /**
     * @param SpotViewQuery $query
     *
     * @return SpotView
     */
    public function handle(SpotViewQuery $query)
    {
        $view = new SpotView(
            $query->spot->getId(),
            $query->spot->getReference(),
            $query->spot->isVisio()
        );

        return $view;
    }
}

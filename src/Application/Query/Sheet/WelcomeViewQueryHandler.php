<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\View\Sheet\WelcomeView;

class WelcomeViewQueryHandler
{
    /**
     * @param WelcomeViewQuery $welcomeViewQuery
     *
     * @return WelcomeView
     */
    public function handle(WelcomeViewQuery $welcomeViewQuery)
    {
        $welcomeView = new WelcomeView();

        if ($welcomeViewQuery->sheet->getPackage()->isPassable()) {
            $welcomeView->hasPackage = true;
        }

        return $welcomeView;
    }
}

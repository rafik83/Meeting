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
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;

class WelcomeViewQueryHandler
{
    /**
     * @var HappeningsAccessChecker
     */
    private $happeningsAccessChecker;

    /**
     * @param HappeningsAccessChecker $happeningsAccessChecker
     */
    public function __construct(HappeningsAccessChecker $happeningsAccessChecker)
    {
        $this->happeningsAccessChecker = $happeningsAccessChecker;
    }

    /**
     * @param WelcomeViewQuery $welcomeViewQuery
     *
     * @return null|WelcomeView
     */
    public function handle(WelcomeViewQuery $welcomeViewQuery): ?WelcomeView
    {
        $welcomeView = new WelcomeView();
        $sheet       = $welcomeViewQuery->sheet;

        if (!$sheet->getEvent()->isWelcomeEnabled()) {
            return null;
        }

        $welcomeView->hasPackage = null !== $sheet->getPackage()
            && $sheet->getPackage()->isPassable();

        $welcomeView->hasProgram = $this->happeningsAccessChecker->allowedToAccess($sheet->getEvent());

        return $welcomeView;
    }
}

<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Dashboard;

class DashboardSheetView
{
    /**
     * @var int
     */
    public $totalEnabledSheets = 0;

    /**
     * @var int
     */
    public $totalParticipants = 0;

    /**
     * @var DashboardSheetTypeView[]
     */
    public $sheetsType;

    /**
     * DashboardSheetView constructor.
     *
     * @param int   $totalEnabledSheets
     * @param int   $totalParticipants
     * @param array $sheetsType
     */
    public function __construct($totalEnabledSheets, $totalParticipants, array $sheetsType)
    {
        $this->totalEnabledSheets = $totalEnabledSheets;
        $this->totalParticipants  = $totalParticipants;
        $this->sheetsType         = $sheetsType;
    }
}

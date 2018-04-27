<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @var DashboardSheetTypeView[]
     */
    public $participantsTypeView;

    /**
     * DashboardSheetView constructor.
     *
     * @param int                      $totalEnabledSheets
     * @param int                      $totalParticipants
     * @param DashboardSheetTypeView[] $sheetsType
     * @param DashboardSheetTypeView[] $participantsTypeView
     */
    public function __construct(
        $totalEnabledSheets,
        $totalParticipants,
        array $sheetsType,
        array $participantsTypeView
    ) {
        $this->totalEnabledSheets   = $totalEnabledSheets;
        $this->totalParticipants    = $totalParticipants;
        $this->sheetsType           = $sheetsType;
        $this->participantsTypeView = $participantsTypeView;
    }
}

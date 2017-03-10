<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Planning\Day\MeetingView;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingViewQueryHandler
{
    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var array */
    private $sheetTitles = [];

    /**
     * @param SheetInfoGuesser $sheetInfoGuesser
     */
    public function __construct(SheetInfoGuesser $sheetInfoGuesser)
    {
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param MeetingViewQuery $query
     *
     * @return MeetingView
     */
    public function handle(MeetingViewQuery $query)
    {
        return new MeetingView(
            $query->meeting->getSlot()->getBegin(),
            $query->meeting->getSlot()->getEnd(),
            $query->meeting->getSpot()->getReference(),
            $this->getSheetMetTitle($query->meeting->getSheetMet($query->sheet))
        );
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    private function getSheetMetTitle(Sheet $sheet)
    {
        if (!isset($this->sheetTitles[$sheet->getId()])) {
            $this->sheetTitles[$sheet->getId()] = $this->sheetInfoGuesser->guessSheetTitle($sheet);
        }

        return $this->sheetTitles[$sheet->getId()];
    }
}

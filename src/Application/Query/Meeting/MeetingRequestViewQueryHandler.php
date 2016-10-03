<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Meeting\MeetingRequestView;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingRequestViewQueryHandler
{
    /**
     * @var Preview
     */
    private $preview;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * MeetingRequestViewQueryHandler constructor.
     *
     * @param Preview          $preview
     * @param SheetInfoGuesser $sheetInfoGuesser
     */
    public function __construct(Preview $preview, SheetInfoGuesser $sheetInfoGuesser)
    {
        $this->preview          = $preview;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    /**
     * @param MeetingRequestViewQuery $query
     *
     * @return MeetingRequestView
     */
    public function handle(MeetingRequestViewQuery $query)
    {
        $sheet    = $this->getViewedSheet($query);
        $previews = $this->preview->getPreview($sheet, $query->locale);

        return new MeetingRequestView(
            $sheet,
            $this->sheetInfoGuesser->guessSheetName($sheet, $query->locale),
            $query->meetingRequest->getState(),
            $sheet->getType()->getTitle($sheet->getEvent()->getAvailableLocale($query->locale)),
            $query->meetingRequest->getCreatedAt(),
            $query->meetingRequest,
            $previews
        );
    }

    /**
     * Guess what sheet need to be displayed
     *
     * @param MeetingRequestViewQuery $query
     *
     * @return Sheet
     */
    private function getViewedSheet(MeetingRequestViewQuery $query)
    {
        if ($query->meetingRequest->getFromSheet() === $query->sheet) {
            return $query->meetingRequest->getToSheet();
        }

        return $query->meetingRequest->getFromSheet();
    }
}

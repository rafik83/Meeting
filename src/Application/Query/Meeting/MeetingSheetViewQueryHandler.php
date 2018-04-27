<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\View\Meeting\MeetingSheetListView;
use Proximum\Vimeet\Application\View\Meeting\MeetingSheetView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class MeetingSheetViewQueryHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var ParticipantsViewQueryHandler
     */
    private $participantsViewQueryHandler;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * MeetingSheetViewQueryHandler constructor.
     *
     * @param RequestRepositoryInterface   $requestRepository
     * @param ParticipantsViewQueryHandler $participantsViewQueryHandler
     * @param SheetInfoGuesser             $sheetInfoGuesser
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        ParticipantsViewQueryHandler $participantsViewQueryHandler,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->requestRepository            = $requestRepository;
        $this->participantsViewQueryHandler = $participantsViewQueryHandler;
        $this->sheetInfoGuesser             = $sheetInfoGuesser;
    }

    /**
     * @param MeetingSheetViewQuery $query
     *
     * @throws SheetNotFoundException
     *
     * @return MeetingSheetListView
     */
    public function handle(MeetingSheetViewQuery $query)
    {
        $meetingsRequest = $this->requestRepository->findAccepted($query->sheet);

        $meetingSheetViews = [];

        foreach ($meetingsRequest as $meeting) {
            $metSheet     = $meeting->getSheetMet($query->sheet);
            $participants = $metSheet->getParticipants()->toArray();

            $sheetTags = $this->sheetInfoGuesser->guessSheetInfos($metSheet, $query->locale);

            $meetingSheetViews[] = new MeetingSheetView(
                $sheetTags[Tag::SHEET_TITLE],
                $sheetTags[Tag::SHEET_ORGANIZATION_CATEGORY],
                $sheetTags[Tag::SHEET_ORGANIZATION_TURNOVER],
                $sheetTags[Tag::SHEET_ORGANIZATION_STAFF],
                $sheetTags[Tag::SHEET_WEBSITE],
                $sheetTags[Tag::SHEET_ADDRESS],
                $sheetTags[Tag::SHEET_ZIPCODE],
                $sheetTags[Tag::SHEET_CITY],
                $sheetTags[Tag::SHEET_COUNTRY],
                $metSheet->getType()->getTitle($query->locale),
                $this->participantsViewQueryHandler->handle(
                    new ParticipantsViewQuery($participants, $query->locale)
                )
            );
        }

        return new MeetingSheetListView($meetingSheetViews, $query->event->getTitle());
    }
}

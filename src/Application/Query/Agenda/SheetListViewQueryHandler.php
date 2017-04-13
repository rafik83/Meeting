<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Indicator\SheetIndicatorsViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Indicator\SheetIndicatorsViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\Indicator\SheetIndicatorsView;
use Proximum\Vimeet\Application\View\Agenda\Admin\SheetView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetListViewQueryHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SheetIndicatorsViewQueryHandler
     */
    private $sheetIndicatorsViewQueryHandler;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * SheetListViewQueryHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetInfoGuesser $sheetInfoGuesser
     * @param SheetIndicatorsViewQueryHandler $sheetIndicatorsViewQueryHandler
     * @param MeetingRepositoryInterface $meetingRepository
     * @param RouterInterface $router
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        SheetIndicatorsViewQueryHandler $sheetIndicatorsViewQueryHandler,
        MeetingRepositoryInterface $meetingRepository,
        RouterInterface $router
    ) {
        $this->sheetRepository   = $sheetRepository;
        $this->sheetInfoGuesser  = $sheetInfoGuesser;
        $this->meetingRepository = $meetingRepository;
        $this->router            = $router;
        $this->sheetIndicatorsViewQueryHandler = $sheetIndicatorsViewQueryHandler;
    }

    /**
     * @param SheetListViewQuery $sheetListViewQuery
     *
     * @return SheetView[]
     */
    public function handle(SheetListViewQuery $sheetListViewQuery)
    {
        $locale    = $sheetListViewQuery->locale;
        $sheetList = [];
        $sheets    = $this->sheetRepository->getSheetsInCatalogByEvent($sheetListViewQuery->event);

        $countMeetings = [];

        if ($sheetListViewQuery->lazyLoadIndicators === true) {
            $countMeetings = $this->meetingRepository->countMeetingsOfEvent($sheetListViewQuery->event);
        }

        foreach ($sheets as $sheet) {
            if (!$sheetListViewQuery->lazyLoadIndicators) {
                $sheetIndicatorsView = $this->sheetIndicatorsViewQueryHandler->handle(new SheetIndicatorsViewQuery($sheet));
            } else {
                $countMeetingsOfSheet = 0;

                if (isset($countMeetings[$sheet->getId()])) {
                    $countMeetingsOfSheet = (int) $countMeetings[$sheet->getId()]['countMeetings'];
                }

                $sheetIndicatorsView = new SheetIndicatorsView(0, 0, 0, 0, 0, $countMeetingsOfSheet);
            }

            /** @var Admin $follower */
            $follower = $sheet->getFollower() ? $sheet->getFollower() : null;

            $sheetList[] = new SheetView(
                $sheet->getId(),
                $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale),
                $sheet->getType()->getTitle($locale),
                count($sheet->getParticipants()),
                $sheetIndicatorsView,
                null !== $follower ? $follower->getFirstname() : null,
                null !== $follower ? $follower->getLastname() : null,
                $this->router->generate(
                    'admin_sheet_details',
                    ['sheet' => $sheet->getId(), 'event' => $sheetListViewQuery->event->getId()]
                )
            );
        }

        $this->sortSheetsByTitle($sheetList);

        return $sheetList;
    }

    /**
     * @param SheetView[] $sheetList
     */
    private function sortSheetsByTitle(array &$sheetList)
    {
        usort($sheetList, function (SheetView $one, SheetView $other) {
            return strcasecmp($one->title, $other->title);
        });
    }
}

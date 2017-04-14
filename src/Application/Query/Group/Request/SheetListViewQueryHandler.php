<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Group\Request;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Exception\Group\Request\NoResultException;
use Proximum\Vimeet\Application\View\Group\Request\SheetListView;
use Proximum\Vimeet\Application\View\Group\Request\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetListViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetInfoGuesserCache */
    private $sheetInfoGuesser;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var SheetViewQueryHandler */
    private $sheetViewQueryHandler;

    /** @var RequestViewQueryHandler */
    private $requestViewQueryHandler;

    /**
     * @param SheetRepositoryInterface   $sheetRepository
     * @param SheetInfoGuesserCache      $sheetInfoGuesser
     * @param RequestRepositoryInterface $requestRepository
     * @param SheetViewQueryHandler      $sheetViewQueryHandler
     * @param RequestViewQueryHandler    $requestViewQueryHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetInfoGuesserCache $sheetInfoGuesser,
        RequestRepositoryInterface $requestRepository,
        SheetViewQueryHandler $sheetViewQueryHandler,
        RequestViewQueryHandler $requestViewQueryHandler
    ) {
        $this->sheetRepository  = $sheetRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->requestRepository = $requestRepository;
        $this->sheetViewQueryHandler = $sheetViewQueryHandler;
        $this->requestViewQueryHandler = $requestViewQueryHandler;
    }

    /**
     * @param SheetListViewQuery $query
     *
     * @return SheetListView
     *
     * @throws NoResultException
     */
    public function handle(SheetListViewQuery $query)
    {
        $groupSheets = $this->sheetRepository->getByGroup($query->group);

        // sheets met by the group sheets
        $sheets = $this->sheetRepository->getSheetsMetBySheets($query->group->getEvent(), $groupSheets);

        $sheetsWithTitle = [];

        foreach ($sheets as $sheet) {
            $sheetsWithTitle[$sheet->getId()] = [
                'title' => $this->sheetInfoGuesser->guessSheetTitle($sheet, $query->locale),
                'sheet' => $sheet,
            ];
        }

        usort($sheetsWithTitle, function ($sheetOne, $sheetTwo) {
            return strcasecmp($sheetOne['title'], $sheetTwo['title']);
        });

        // split the sheetsWithTitle into multiple chunk with the limit provided
        // it is used as the pagination
        // the chunks array is used after to count the total page possible
        $chunks = array_chunk($sheetsWithTitle, $query->limit);

        if (!isset($chunks[$query->page - 1])) {
            // If the page is 1 and there is no result, show no result instead of 404
            if ($query->page === 1) {
                return new SheetListView(
                    $query->group->getId(),
                    $query->group->getTitle(),
                    [],
                    $query->page,
                    0
                );
            }

            throw new NoResultException();
        }

        /** @var Sheet[] $sheetsMet */
        $sheetsMet = array_map(function ($sheetMet) {
            return $sheetMet['sheet'];
        }, $chunks[$query->page - 1]);

        $sheetViews = $this->getSheetViewsWithRequest(
            $query->group->getEvent(),
            $groupSheets,
            $sheetsMet,
            $query->locale
        );

        return new SheetListView(
            $query->group->getId(),
            $query->group->getTitle(),
            $sheetViews,
            $query->page, // current page
            count($chunks) // total page
        );
    }

    /**
     * Creates the SheetViews and returns them with there requests
     *
     * @param Event   $event
     * @param Sheet[] $groupSheets
     * @param Sheet[] $sheetsMet
     * @param string  $locale
     *
     * @return SheetView[]
     */
    private function getSheetViewsWithRequest(Event $event, array &$groupSheets, array &$sheetsMet, $locale)
    {
        /** @var SheetView[] $sheetViews */
        $sheetViews = [];

        foreach ($sheetsMet as $sheetMet) {
            $sheetViews[$sheetMet->getId()] = $this->sheetViewQueryHandler->handle(
                new SheetViewQuery($sheetMet, $locale)
            );
        }

        $requests = $this->requestRepository->getRequestsOfSheetsWithSheets(
            $event,
            $sheetsMet,
            $groupSheets
        );

        $this->addRequestToSheetViews($requests, $groupSheets, $sheetViews, $locale);

        return $sheetViews;
    }

    /**
     * Add RequestView to SheetView
     * @param Request[]   $requests
     * @param Sheet[]     $groupSheets
     * @param SheetView[] $sheetViews
     * @param string      $locale
     */
    private function addRequestToSheetViews(array &$requests, array &$groupSheets, array &$sheetViews, $locale)
    {
        foreach ($requests as $request) {
            $sheetMet = $this->getSheetMet($request, $groupSheets);

            $requestView = $this->requestViewQueryHandler->handle(
                new RequestViewQuery($sheetMet, $request, $locale)
            );

            if (isset($sheetViews[$sheetMet->getId()])) {
                $sheetViews[$sheetMet->getId()]->addRequest($requestView);
            }
        }
    }

    /**
     * @param Request $request
     * @param Sheet[] $groupSheet
     *
     * @return Sheet
     */
    private function getSheetMet(Request $request, array &$groupSheet)
    {
        // If the from sheet is not in the group sheet, then the sheet met is the from sheet
        if (!isset($groupSheet[$request->getFromSheet()->getId()])) {
            return $request->getFromSheet();
        }

        // If the to sheet is not in the group sheet, then the sheet met is the to sheet
        if (!isset($groupSheet[$request->getToSheet()->getId()])) {
            return $request->getToSheet();
        }

        // Otherwise, it means that it is a sheet from the group that meet another sheet from the group
        return $request->getFromSheet();
    }
}

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
use Proximum\Vimeet\Application\Exception\Group\Request\SheetNotMetException;
use Proximum\Vimeet\Application\View\Group\Request\SheetListView;
use Proximum\Vimeet\Application\View\Group\Request\SheetView;
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

        $chuncks = array_chunk($sheetsWithTitle, $query->limit);

        if (!isset($chuncks[$query->page - 1])) {
            throw new NoResultException();
        }

        /** @var Sheet[] $sheetsMet */
        $sheetsMet = array_map(function ($sheetMet) {
            return $sheetMet['sheet'];
        }, $chuncks[$query->page - 1]);


        $requests = $this->requestRepository->getRequestsOfSheetsWithSheets($query->group->getEvent(), $sheetsMet, $groupSheets);

        /** @var SheetView[] $sheetViews */
        $sheetViews = [];

        foreach ($requests as $request) {
            $sheetMet = $this->getSheetMet($request, $groupSheets);

            if (!isset($sheetViews[$sheetMet->getId()])) {
                $sheetViews[$sheetMet->getId()] = $this->sheetViewQueryHandler->handle(
                    new SheetViewQuery($sheetMet, $query->locale)
                );
            }

            $requestView = $this->requestViewQueryHandler->handle(
                new RequestViewQuery($sheetMet, $request, $query->locale)
            );

            $sheetViews[$sheetMet->getId()]->addRequest($requestView);
        }

        return new SheetListView(
            $query->group->getId(),
            $query->group->getTitle(),
            $sheetViews
        );
    }

    /**
     * @param Request $request
     * @param Sheet[] $groupSheet
     *
     * @return Sheet
     * @throws SheetNotMetException
     */
    private function getSheetMet(Request $request, array &$groupSheet)
    {
        if (!isset($groupSheet[$request->getFromSheet()->getId()])) {
            return $request->getFromSheet();
        }

        if (!isset($groupSheet[$request->getToSheet()->getId()])) {
            return $request->getToSheet();
        }

        throw new SheetNotMetException();
    }
}

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
use Proximum\Vimeet\Domain\Model\Sheet;
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
     * SheetListViewQueryHandler constructor.
     *
     * @param SheetRepositoryInterface        $sheetRepository
     * @param SheetInfoGuesser                $sheetInfoGuesser
     * @param SheetIndicatorsViewQueryHandler $sheetIndicatorsViewQueryHandler
     * @param RouterInterface                 $router
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        SheetIndicatorsViewQueryHandler $sheetIndicatorsViewQueryHandler,
        RouterInterface $router
    ) {
        $this->sheetRepository     = $sheetRepository;
        $this->sheetInfoGuesser    = $sheetInfoGuesser;
        $this->router              = $router;
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

        foreach ($sheets as $sheet) {
            $sheetIndicatorsView = new SheetIndicatorsView();

            if (!$sheetListViewQuery->lazyLoadIndicators) {
                $sheetIndicatorsView = $this->sheetIndicatorsViewQueryHandler->handle(new SheetIndicatorsViewQuery($sheet));
            }

            $sheetList[] = new SheetView(
                $sheet->getId(),
                $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale),
                $sheet->getType()->getTitle($locale),
                count($sheet->getParticipants()),
                $sheetIndicatorsView,
                null !== $sheet->getFollower() ? $sheet->getFollower()->getDisplayName() : null,
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

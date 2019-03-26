<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use DateTimeInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;
use Proximum\Vimeet\Domain\Participant\Catalog\HasAccessToCatalog;
use Proximum\Vimeet\Domain\Sheet\Catalog\CanSeeOtherSheets;

class CatalogSubmenuViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * @var DateTimeInterface
     */
    private $datetime;
    
    /** @var CanSeeOtherSheets */
    private $canSeeOtherSheets;
    
    /**
     * CatalogSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     * @param DateTimeInterface          $datetime
     * @param CanSeeOtherSheets          $canSeeOtherSheets
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        DateTimeInterface $datetime,
        CanSeeOtherSheets $canSeeOtherSheets
    ) {
        $this->navigationBuilder = $navigationBuilder;
        $this->datetime = $datetime;
        $this->canSeeOtherSheets = $canSeeOtherSheets;
    }

    /**
     * @param CatalogSubmenuViewQuery $query
     *
     * @return SubmenuButtonView[]
     */
    public function handle(CatalogSubmenuViewQuery $query)
    {
        $buttonViews = [];

        $catalogOnlineDate = $query->event->getConfiguration()->getCatalogOnlineDate();

        if (null !== $catalogOnlineDate && $catalogOnlineDate <= $this->datetime && $query->sheet->isInInternalCatalog()) {
            if ($this->canSeeOtherSheets->isSatisfiedBy($query->sheet)) {
                $catalogTitle = 'navigation.category.catalog';
    
                if (isset($query->staticFormulationsIndexedByCategory[Category::CATALOG])) {
                    $catalogTitle = $query->staticFormulationsIndexedByCategory[Category::CATALOG]->getTitle($query->locale);
                }
    
                $buttonViews[] = new SubmenuButtonView(
                    Category::CATALOG_ICON,
                    $catalogTitle,
                    $this->navigationBuilder->getRoute('event_catalog_index', ['sheet' => $query->sheet->getId()]),
                    Route::isCatalog($query->route),
                    false,
                    true
                );
            }
            $meetingTitle = 'navigation.category.meeting';

            if (isset($query->staticFormulationsIndexedByCategory[Category::MEETING])) {
                $meetingTitle = $query->staticFormulationsIndexedByCategory[Category::MEETING]->getTitle($query->locale);
            }

            $buttonViews[] = new SubmenuButtonView(
                Category::MEETING_ICON,
                $meetingTitle,
                $this->navigationBuilder->getRoute(
                    $query->sheet->hasLinkedSheets()
                        ? 'event_meeting_request_merged_list'
                        : 'event_meeting_list_request',
                    [
                        'sheet' => $query->sheet->getId(),
                    ]
                ),
                Route::isMeetingRequest($query->route),
                false,
                true
            );
        }

        return $buttonViews;
    }
}

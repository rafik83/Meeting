<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class AgendaSubmenuViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;
    
    /**
     * @var HappeningsAccessChecker
     */
    private $happeningsAccessChecker;

    /**
     * CatalogSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     * @param HappeningsAccessChecker    $happeningsAccessChecker
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        HappeningsAccessChecker $happeningsAccessChecker
    ) {
        $this->navigationBuilder       = $navigationBuilder;
        $this->happeningsAccessChecker = $happeningsAccessChecker;
    }

    /**
     * @param AgendaSubmenuViewQuery $query
     *
     * @return SubmenuButtonView[]
     */
    public function handle(AgendaSubmenuViewQuery $query)
    {
        $buttonViews = [];

        $buttonViews[] = new SubmenuButtonView(
            Category::AGENDA_ICON,
            'agenda.title',
            $this->navigationBuilder->getRoute('event_agenda'),
            Route::isAgenda($query->route)
        );

        if ($this->happeningsAccessChecker->allowedToAccess($query->event)) {
            $buttonViews[] = new SubmenuButtonView(
                Category::PLANNING_ICON,
                'program.title',
                $this->navigationBuilder->getRoute('happening_program'),
                Route::isProgram($query->route)
            );
        }

        return $buttonViews;
    }
}

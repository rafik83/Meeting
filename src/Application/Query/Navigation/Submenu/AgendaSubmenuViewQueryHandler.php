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
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class AgendaSubmenuViewQueryHandler
{
    /**
     * @var NavigationBuilderInterface
     */
    private $navigationBuilder;

    /**
     * CatalogSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     */
    public function __construct(NavigationBuilderInterface $navigationBuilder)
    {
        $this->navigationBuilder = $navigationBuilder;
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

        $buttonViews[] = new SubmenuButtonView(
            Category::PLANNING_ICON,
            'program.title',
            $this->navigationBuilder->getRoute('happening_program'),
            Route::isProgram($query->route)
        );

        return $buttonViews;
    }

}

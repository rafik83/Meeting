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
use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
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
     * @var AgendaAccessChecker
     */
    private $agendaAccessChecker;

    /**
     * CatalogSubmenuViewQueryHandler constructor.
     *
     * @param NavigationBuilderInterface $navigationBuilder
     * @param HappeningsAccessChecker    $happeningsAccessChecker
     * @param AgendaAccessChecker        $agendaAccessChecker
     */
    public function __construct(
        NavigationBuilderInterface $navigationBuilder,
        HappeningsAccessChecker $happeningsAccessChecker,
        AgendaAccessChecker $agendaAccessChecker
    ) {
        $this->navigationBuilder       = $navigationBuilder;
        $this->happeningsAccessChecker = $happeningsAccessChecker;
        $this->agendaAccessChecker     = $agendaAccessChecker;
    }

    /**
     * @param AgendaSubmenuViewQuery $query
     *
     * @return SubmenuButtonView[]
     */
    public function handle(AgendaSubmenuViewQuery $query)
    {
        $buttonViews = [];

        if ($this->agendaAccessChecker->allowedToAccess($query->event)) {
            $buttonViews[] = new SubmenuButtonView(
                Category::AGENDA_ICON,
                'agenda.title',
                $this->navigationBuilder->getRoute('event_agenda', ['sheet' => $query->sheet->getId()]),
                Route::isAgenda($query->route),
                false,
                true
            );
        }

        if ($this->happeningsAccessChecker->allowedToAccess($query->event)) {
            $buttonViews[] = new SubmenuButtonView(
                Category::PROGRAM_ICON,
                'program.title',
                $this->navigationBuilder->getRoute('happening_program', ['sheet' => $query->sheet->getId()]),
                Route::isProgram($query->route),
                false,
                true
            );
        }

        return $buttonViews;
    }
}

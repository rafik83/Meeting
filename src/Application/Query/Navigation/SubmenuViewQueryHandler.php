<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\AgendaSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\AgendaSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CatalogSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CatalogSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\SheetSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\SheetSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuView;

class SubmenuViewQueryHandler
{
    /**
     * @var SheetGuesser
     */
    private $sheetGuesser;

    /**
     * @var SheetSubmenuViewQueryHandler
     */
    private $sheetSubmenuViewQueryHandler;

    /**
     * @var CatalogSubmenuViewQueryHandler
     */
    private $catalogSubmenuViewQueryHandler;

    /**
     * @var AgendaSubmenuViewQueryHandler
     */
    private $agendaSubmenuViewQueryHandler;

    /**
     * SubmenuViewQueryHandler constructor.
     *
     * @param SheetGuesser                   $sheetGuesser
     * @param SheetSubmenuViewQueryHandler   $sheetSubmenuViewQueryHandler
     * @param CatalogSubmenuViewQueryHandler $catalogSubmenuViewQueryHandler
     * @param AgendaSubmenuViewQueryHandler  $agendaSubmenuViewQueryHandler
     */
    public function __construct(
        SheetGuesser $sheetGuesser,
        SheetSubmenuViewQueryHandler $sheetSubmenuViewQueryHandler,
        CatalogSubmenuViewQueryHandler $catalogSubmenuViewQueryHandler,
        AgendaSubmenuViewQueryHandler $agendaSubmenuViewQueryHandler
    ) {
        $this->sheetGuesser                   = $sheetGuesser;
        $this->sheetSubmenuViewQueryHandler   = $sheetSubmenuViewQueryHandler;
        $this->catalogSubmenuViewQueryHandler = $catalogSubmenuViewQueryHandler;
        $this->agendaSubmenuViewQueryHandler  = $agendaSubmenuViewQueryHandler;
    }

    /**
     * @param SubmenuViewQuery $query
     *
     * @return SubmenuView
     */
    public function handle(SubmenuViewQuery $query)
    {
        $sheet = $this->sheetGuesser->getUserSheet(
            $query->user,
            $query->event,
            $query->locale
        );

        if (Route::isCatalog($query->route) === true || Route::isMeetingRequest($query->route) === true) {
            $buttonViews = $this->catalogSubmenuViewQueryHandler->handle(
                new CatalogSubmenuViewQuery(
                    $query->user,
                    $query->event,
                    $query->locale,
                    $sheet,
                    $query->route
                )
            );

            return new SubmenuView($buttonViews);
        }

        if (Route::isProgram($query->route) === true || Route::isAgenda($query->route) === true) {
            $buttonViews = $this->agendaSubmenuViewQueryHandler->handle(
                new AgendaSubmenuViewQuery(
                    $query->user,
                    $query->event,
                    $query->locale,
                    $sheet,
                    $query->route
                )
            );

            return new SubmenuView($buttonViews);
        }

        $buttonViews = $this->sheetSubmenuViewQueryHandler->handle(
            new SheetSubmenuViewQuery(
                $query->user,
                $query->event,
                $query->locale,
                $sheet,
                $query->route
            )
        );

        return new SubmenuView($buttonViews);
    }
}

<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\AgendaSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\AgendaSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CatalogSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CatalogSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\PackageSubmenuButtonViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\PackageSubmenuButtonViewQueryHandler;
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
     * @var PackageSubmenuButtonViewQueryHandler
     */
    private $packageSubmenuButtonViewQueryHandler;

    /**
     * SubmenuViewQueryHandler constructor.
     *
     * @param SheetGuesser                         $sheetGuesser
     * @param SheetSubmenuViewQueryHandler         $sheetSubmenuViewQueryHandler
     * @param CatalogSubmenuViewQueryHandler       $catalogSubmenuViewQueryHandler
     * @param AgendaSubmenuViewQueryHandler        $agendaSubmenuViewQueryHandler
     * @param PackageSubmenuButtonViewQueryHandler $packageSubmenuButtonViewQueryHandler
     */
    public function __construct(
        SheetGuesser $sheetGuesser,
        SheetSubmenuViewQueryHandler $sheetSubmenuViewQueryHandler,
        CatalogSubmenuViewQueryHandler $catalogSubmenuViewQueryHandler,
        AgendaSubmenuViewQueryHandler $agendaSubmenuViewQueryHandler,
        PackageSubmenuButtonViewQueryHandler $packageSubmenuButtonViewQueryHandler
    ) {
        $this->sheetGuesser                   = $sheetGuesser;
        $this->sheetSubmenuViewQueryHandler   = $sheetSubmenuViewQueryHandler;
        $this->catalogSubmenuViewQueryHandler = $catalogSubmenuViewQueryHandler;
        $this->agendaSubmenuViewQueryHandler  = $agendaSubmenuViewQueryHandler;
        $this->packageSubmenuButtonViewQueryHandler = $packageSubmenuButtonViewQueryHandler;
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

        $buttonsViews = [];

        $sheetButtonViews = $this->sheetSubmenuViewQueryHandler->handle(
            new SheetSubmenuViewQuery(
                $query->user,
                $query->event,
                $query->locale,
                $sheet,
                $query->route
            )
        );

        $buttonsViews = array_merge($buttonsViews, $sheetButtonViews);

        $catalogButtonViews = $this->catalogSubmenuViewQueryHandler->handle(
            new CatalogSubmenuViewQuery(
                $query->user,
                $query->event,
                $query->locale,
                $sheet,
                $query->route
            )
        );

        $buttonsViews = array_merge($buttonsViews, $catalogButtonViews);

        $agendaButtonViews = $this->agendaSubmenuViewQueryHandler->handle(
            new AgendaSubmenuViewQuery(
                $query->user,
                $query->event,
                $query->locale,
                $sheet,
                $query->route
            )
        );

        $buttonsViews = array_merge($buttonsViews, $agendaButtonViews);

        // Package button
        $packageSubmenuButtonView = $this->packageSubmenuButtonViewQueryHandler->handle(
            new PackageSubmenuButtonViewQuery($sheet, $query->route)
        );

        if (null !== $packageSubmenuButtonView) {
            $buttonsViews[] = $packageSubmenuButtonView;
        }

        return new SubmenuView($buttonsViews);
    }
}

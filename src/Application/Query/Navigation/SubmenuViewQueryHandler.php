<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation;

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
     * @param SheetSubmenuViewQueryHandler         $sheetSubmenuViewQueryHandler
     * @param CatalogSubmenuViewQueryHandler       $catalogSubmenuViewQueryHandler
     * @param AgendaSubmenuViewQueryHandler        $agendaSubmenuViewQueryHandler
     * @param PackageSubmenuButtonViewQueryHandler $packageSubmenuButtonViewQueryHandler
     */
    public function __construct(
        SheetSubmenuViewQueryHandler $sheetSubmenuViewQueryHandler,
        CatalogSubmenuViewQueryHandler $catalogSubmenuViewQueryHandler,
        AgendaSubmenuViewQueryHandler $agendaSubmenuViewQueryHandler,
        PackageSubmenuButtonViewQueryHandler $packageSubmenuButtonViewQueryHandler
    ) {
        $this->sheetSubmenuViewQueryHandler         = $sheetSubmenuViewQueryHandler;
        $this->catalogSubmenuViewQueryHandler       = $catalogSubmenuViewQueryHandler;
        $this->agendaSubmenuViewQueryHandler        = $agendaSubmenuViewQueryHandler;
        $this->packageSubmenuButtonViewQueryHandler = $packageSubmenuButtonViewQueryHandler;
    }

    /**
     * @param SubmenuViewQuery $submenuViewQuery
     *
     * @return SubmenuView
     */
    public function handle(SubmenuViewQuery $submenuViewQuery)
    {
        if (null === $submenuViewQuery->sheet || null === $submenuViewQuery->user) {
            return new SubmenuView([]);
        }

        $buttonsViews = [];

        $sheetButtonViews = $this->sheetSubmenuViewQueryHandler->handle(
            new SheetSubmenuViewQuery(
                $submenuViewQuery->user,
                $submenuViewQuery->event,
                $submenuViewQuery->locale,
                $submenuViewQuery->sheet,
                $submenuViewQuery->route
            )
        );

        $buttonsViews = array_merge($buttonsViews, $sheetButtonViews);

        $catalogButtonViews = $this->catalogSubmenuViewQueryHandler->handle(
            new CatalogSubmenuViewQuery(
                $submenuViewQuery->user,
                $submenuViewQuery->event,
                $submenuViewQuery->locale,
                $submenuViewQuery->sheet,
                $submenuViewQuery->route
            )
        );

        $buttonsViews = array_merge($buttonsViews, $catalogButtonViews);

        $agendaButtonViews = $this->agendaSubmenuViewQueryHandler->handle(
            new AgendaSubmenuViewQuery(
                $submenuViewQuery->user,
                $submenuViewQuery->event,
                $submenuViewQuery->locale,
                $submenuViewQuery->sheet,
                $submenuViewQuery->route
            )
        );

        $buttonsViews = array_merge($buttonsViews, $agendaButtonViews);

        // Package button
        $packageSubmenuButtonView = $this->packageSubmenuButtonViewQueryHandler->handle(
            new PackageSubmenuButtonViewQuery($submenuViewQuery->sheet, $submenuViewQuery->route)
        );

        if (null !== $packageSubmenuButtonView) {
            $buttonsViews[] = $packageSubmenuButtonView;
        }

        return new SubmenuView($buttonsViews);
    }
}

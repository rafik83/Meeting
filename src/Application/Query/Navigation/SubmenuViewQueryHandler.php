<?php

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\AgendaSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\BadgeScanSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\BadgeSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\CatalogSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\ContactsSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\LeniBadgeLinkSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\NetworkingSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\PackageSubmenuButtonViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\SheetSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\UserCtaSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\VisioSubmenuViewQuery;
use Proximum\Vimeet\Application\View\Navigation\SubmenuView;

class SubmenuViewQueryHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(QueryBusInterface $queryBus)
    {
        $this->queryBus = $queryBus;
    }

    public function handle(SubmenuViewQuery $submenuViewQuery): SubmenuView
    {
        if (null === $submenuViewQuery->sheet || null === $submenuViewQuery->user) {
            return new SubmenuView([]);
        }

        $buttonsViews = [];

        $badgeSubmenuView = $this->queryBus->handle(
            new BadgeSubmenuViewQuery(
                $submenuViewQuery->user,
                $submenuViewQuery->event,
                $submenuViewQuery->locale,
                $submenuViewQuery->sheet,
                $submenuViewQuery->route,
                $submenuViewQuery->staticFormulationsIndexByCategories[Category::BADGE] ?? null
            )
        );

        $networkingButtonViews = $this->queryBus->handle(
            new NetworkingSubmenuViewQuery(
                $submenuViewQuery->user,
                $submenuViewQuery->event,
                $submenuViewQuery->locale,
                $submenuViewQuery->sheet,
                $submenuViewQuery->route,
                $submenuViewQuery->staticFormulationsIndexByCategories[Category::NETWORKING] ?? null
            )
        );

        if (null !== $networkingButtonViews) {
            $buttonsViews[] = $networkingButtonViews;
        }

        if (null !== $badgeSubmenuView) {
            $buttonsViews[] = $badgeSubmenuView;
        } else {
            // If badge available do not show sheet links
            $sheetButtonViews = $this->queryBus->handle(
                new SheetSubmenuViewQuery(
                    $submenuViewQuery->user,
                    $submenuViewQuery->event,
                    $submenuViewQuery->locale,
                    $submenuViewQuery->sheet,
                    $submenuViewQuery->route,
                    $submenuViewQuery->staticFormulationsIndexByCategories[Category::SHEET] ?? null
                )
            );

            $buttonsViews = array_merge($buttonsViews, $sheetButtonViews);
        }

        $badgeScanButtonView = $this->queryBus->handle(
            new BadgeScanSubmenuViewQuery(
                $submenuViewQuery->user,
                $submenuViewQuery->event,
                $submenuViewQuery->locale,
                $submenuViewQuery->sheet,
                $submenuViewQuery->route,
                $submenuViewQuery->staticFormulationsIndexByCategories[Category::BADGE_SCAN] ?? null
            )
        );

        if (null !== $badgeScanButtonView) {
            $buttonsViews[] = $badgeScanButtonView;
        }

        $contactButtonView = $this->queryBus->handle(
            new ContactsSubmenuViewQuery(
                $submenuViewQuery->user,
                $submenuViewQuery->event,
                $submenuViewQuery->locale,
                $submenuViewQuery->sheet,
                $submenuViewQuery->route,
                $submenuViewQuery->staticFormulationsIndexByCategories[Category::CONTACT_LIST] ?? null
            )
        );

        if (null !== $contactButtonView) {
            $buttonsViews[] = $contactButtonView;
        }

        $leniBadgeLinkButtonView = $this->queryBus->handle(
            new LeniBadgeLinkSubmenuViewQuery(
                $submenuViewQuery->user,
                $submenuViewQuery->event,
                $submenuViewQuery->locale,
                $submenuViewQuery->sheet
            )
        );

        if (null !== $leniBadgeLinkButtonView) {
            $buttonsViews[] = $leniBadgeLinkButtonView;
        }

        $catalogButtonViews = $this->queryBus->handle(
            new CatalogSubmenuViewQuery(
                $submenuViewQuery->user,
                $submenuViewQuery->event,
                $submenuViewQuery->locale,
                $submenuViewQuery->sheet,
                $submenuViewQuery->route,
                $submenuViewQuery->staticFormulationsIndexByCategories
            )
        );

        $buttonsViews = array_merge($buttonsViews, $catalogButtonViews);

        $customButtonViews = $this->queryBus->handle(
            new UserCtaSubmenuViewQuery(
                $submenuViewQuery->user,
                $submenuViewQuery->event,
                $submenuViewQuery->locale,
                $submenuViewQuery->sheet
            )
        );

        $buttonsViews = array_merge($buttonsViews, $customButtonViews);

        $agendaButtonViews = $this->queryBus->handle(
            new AgendaSubmenuViewQuery(
                $submenuViewQuery->user,
                $submenuViewQuery->event,
                $submenuViewQuery->locale,
                $submenuViewQuery->sheet,
                $submenuViewQuery->route,
                $submenuViewQuery->staticFormulationsIndexByCategories
            )
        );

        $buttonsViews = array_merge($buttonsViews, $agendaButtonViews);

        $packageSubmenuButtonView = $this->queryBus->handle(
            new PackageSubmenuButtonViewQuery(
                $submenuViewQuery->sheet,
                $submenuViewQuery->route,
                $submenuViewQuery->locale,
                $submenuViewQuery->staticFormulationsIndexByCategories[Category::PACKAGE] ?? null
            )
        );

        if (null !== $packageSubmenuButtonView) {
            $buttonsViews[] = $packageSubmenuButtonView;
        }

        $visioSubmenuButtonView = $this->queryBus->handle(
            new VisioSubmenuViewQuery(
                $submenuViewQuery->event,
                $submenuViewQuery->sheet,
                $submenuViewQuery->locale,
                $submenuViewQuery->route,
                $submenuViewQuery->staticFormulationsIndexByCategories[Category::VISIO] ?? null
            )
        );

        if (null !== $visioSubmenuButtonView) {
            $buttonsViews[] = $visioSubmenuButtonView;
        }

        return new SubmenuView($buttonsViews, $customButtonViews);
    }
}

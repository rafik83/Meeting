<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use IntlDateFormatter;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Application\View\Navigation\StateButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Navigation\NavigationBuilderInterface;

class PlanningViewQueryHandler
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
     * PlanningViewQueryHandler constructor.
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
     * @param PlanningViewQuery $planningQuery
     *
     * @return CategoryView
     */
    public function handle(PlanningViewQuery $planningQuery)
    {
        $schedulePublishDate = $planningQuery
            ->sheet
            ->getEvent()
            ->getConfiguration()
            ->getSchedulePublishDate();

        $happeningOpenDate = $planningQuery
            ->sheet
            ->getEvent()
            ->getConfiguration()
            ->getHappeningsOpenDate();

        $linksView = [];

        if ($schedulePublishDate === null) {
            $linksView[] = new LinkView('navigation.links.incoming', null);
        } else {
            $formatter = new IntlDateFormatter(
                $planningQuery->locale,
                IntlDateFormatter::LONG,
                IntlDateFormatter::LONG
            );
            $formatter->setPattern('d MMMM Y');

            $happeningOpenDateFormatted = $formatter->format($happeningOpenDate);

            $agendaRoute = null;

            if ($this->happeningsAccessChecker->allowedToAccess($planningQuery->sheet->getEvent())) {
                $agendaRoute = $this->navigationBuilder->getRoute('event_agenda');
            }

            $linksView[] = new LinkView(
                'navigation.links.planning.available_date',
                $agendaRoute,
                null,
                new StateButtonView(false, $happeningOpenDateFormatted ? $happeningOpenDateFormatted : '')
            );

            $schedulePublishDateFormatted = $formatter->format($schedulePublishDate);

            $linksView[] = new LinkView(
                'navigation.links.planning.final_date',
                null,
                null,
                new StateButtonView(false, $schedulePublishDateFormatted ? $schedulePublishDateFormatted : '')
            );
        }

        return new CategoryView(Category::PLANNING, Category::PLANNING_ICON, $linksView);
    }
}

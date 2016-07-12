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
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Navigation\NavigationBuilder;

class PlanningViewQueryHandler
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var NavigationBuilder
     */
    private $navigationBuilder;

    /**
     * PlanningViewQueryHandler constructor.
     *
     * @param \DateTimeInterface $dateTime
     * @param NavigationBuilder  $navigationBuilder
     */
    public function __construct(\DateTimeInterface $dateTime, NavigationBuilder $navigationBuilder)
    {
        $this->dateTime          = $dateTime;
        $this->navigationBuilder = $navigationBuilder;
    }

    /**
     * @param PlanningViewQuery $planningQuery
     *
     * @return CategoryView|null
     */
    public function handle(PlanningViewQuery $planningQuery)
    {
        $schedulePublishDate = $planningQuery->sheet->getEvent()
                                                    ->getConfiguration()
                                                    ->getSchedulePublishDate();
        $happeningOpenDate   = $planningQuery->sheet->getEvent()
                                                    ->getConfiguration()
                                                    ->getHappeningsOpenDate();

        $linksView = [];

        if ($schedulePublishDate === null) {
            $linksView[] = new LinkView('navigation.links.incoming', null);
        } else {
            $formatter = new IntlDateFormatter($planningQuery->locale, IntlDateFormatter::LONG,
                IntlDateFormatter::LONG);
            $formatter->setPattern('d MMMM Y');

            $linksView[] = new LinkView(
                'navigation.links.planning.available_date',
                null,
                null,
                new StateButtonView(false, $formatter->format($happeningOpenDate))
            );

            $linksView[] = new LinkView(
                'navigation.links.planning.final_date',
                null,
                null,
                new StateButtonView(false, $formatter->format($schedulePublishDate))
            );
        }

        return new CategoryView(Category::PLANNING, Category::PLANNING_ICON, $linksView);
    }
}

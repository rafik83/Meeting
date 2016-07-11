<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Navigation\Category;

use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
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

        if ($schedulePublishDate === null) {
            return null;
        }

        $linksView = [];

        if ($this->dateTime < $schedulePublishDate) {
            $linksView[] = new LinkView(
                'navigation.links.planning.available_date',
                null
            );
        } else {
            $linksView[] = new LinkView(
                'navigation.links.planning.unavailability',
                $this->navigationBuilder->getRoute('event_sheet_schedule_add_unavailability', [
                    'sheet' => $planningQuery->sheet->getId(),
                ])
            );
            $linksView[] = new LinkView(
                'navigation.links.planning.conference',
                null
            );
            $linksView[] = new LinkView(
                'navigation.links.planning.flash_presentation',
                null
            );
            $linksView[] = new LinkView(
                'navigation.links.planning.my_schedule',
                $this->navigationBuilder->getRoute('event_sheet_schedule', [
                    'sheet' => $planningQuery->sheet->getId(),
                ])
            );
        }

        return new CategoryView(
            Category::PLANNING,
            Category::PLANNING_ICON,
            $linksView
        );
    }
}

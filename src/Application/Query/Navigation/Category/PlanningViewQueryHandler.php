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

class PlanningViewQueryHandler
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * PlanningViewQueryHandler constructor.
     *
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
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
                ''
            );
        } else {
            $linksView[] = new LinkView(
                'navigation.links.planning.unavailability',
                ''
            );
            $linksView[] = new LinkView(
                'navigation.links.planning.conference',
                ''
            );
            $linksView[] = new LinkView(
                'navigation.links.planning.flash_presentation',
                ''
            );
            $linksView[] = new LinkView(
                'navigation.links.planning.my_schedule',
                ''
            );
            $linksView[] = new LinkView(
                'navigation.links.planning.all_schedule',
                ''
            );
        }

        return new CategoryView(
            Category::PLANNING,
            Category::PLANNING_ICON,
            $linksView
        );
    }
}

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

class HappeningViewQueryHandler
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
     * HappeningViewQueryHandler constructor.
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
     *
     * @param HappeningViewQuery $happeningViewQuery
     *
     * @return CategoryView
     */
    public function handle(HappeningViewQuery $happeningViewQuery)
    {
        $happeningOpenDate = $happeningViewQuery->sheet->getEvent()
                                                       ->getConfiguration()
                                                       ->getHappeningsOpenDate();

        if ($this->dateTime < $happeningOpenDate) {
            return null;
        }

        $linksView = [];

        $linksView[] = new LinkView(
            'navigation.links.happening.proposal',
            null
        );

        $linksView[] = new LinkView(
            'navigation.links.happening.waiting',
            null
        );

        $linksView[] = new LinkView(
            'navigation.links.happening.accept',
            null
        );

        $linksView[] = new LinkView(
            'navigation.links.happening.decline',
            null
        );

        return new CategoryView(
            Category::HAPPENING,
            Category::HAPPENING_ICON,
            $linksView
        );
    }
}

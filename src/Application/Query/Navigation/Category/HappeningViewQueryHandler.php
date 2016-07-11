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

class HappeningViewQueryHandler
{
    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * HappeningViewQueryHandler constructor.
     *
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(\DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
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

        $linksView   = [];

        $linksView[] = new LinkView(
            'navigation.links.happening.proposal',
            ''
        );

        $linksView[] = new LinkView(
            'navigation.links.happening.waiting',
            ''
        );

        $linksView[] = new LinkView(
            'navigation.links.happening.accept',
            ''
        );

        $linksView[] = new LinkView(
            'navigation.links.happening.decline',
            ''
        );

        return new CategoryView(
            Category::HAPPENING,
            Category::HAPPENING_ICON,
            $linksView
        );
    }
}

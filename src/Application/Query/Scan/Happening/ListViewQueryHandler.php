<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Scan\Happening;

use Proximum\Vimeet\Application\View\Scan\Happening\HappeningView;
use Proximum\Vimeet\Application\View\Scan\Happening\ListView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class ListViewQueryHandler
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    public function __construct(HappeningRepositoryInterface $happeningRepository)
    {
        $this->happeningRepository = $happeningRepository;
    }

    public function handle(ListViewQuery $query): ListView
    {
        $happenings = $this->happeningRepository->findByEvent($query->event);

        uasort($happenings, function (Happening $one, Happening $another) {
            return $one->getBegin() <=> $another->getBegin();
        });

        $locale = $query->event->getAvailableLocale($query->locale);

        $happeningViews = [];
        foreach ($happenings as $happening) {
            $happeningViews[] = new HappeningView(
                $happening->getId(),
                $happening->getTitle($locale),
                $happening->getBegin()
            );
        }

        return new ListView($happeningViews);
    }
}

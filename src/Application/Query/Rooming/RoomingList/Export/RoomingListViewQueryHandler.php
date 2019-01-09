<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export;

use Proximum\Vimeet\Application\View\Rooming\ExportList\RoomingListView;
use Proximum\Vimeet\Domain\Repository\Rooming\StayRepositoryInterface;

class RoomingListViewQueryHandler
{
    /** @var StayRepositoryInterface */
    private $stayRepository;

    public function __construct(StayRepositoryInterface $stayRepository)
    {
        $this->stayRepository = $stayRepository;
    }

    public function handle(RoomingListViewQuery $query): RoomingListView
    {
        $locale = $query->event->getAvailableLocale($query->locale);
    }
}

<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Rooming\ExportList;

class RoomingListView
{
    /** @var string */
    public $locale;

    /** @var StayView[] */
    public $stayViews;

    public function __construct(string $locale, array $stayViews = [])
    {
        $this->stayViews = $stayViews;
        $this->locale = $locale;
    }
}

<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Rooming\Accommodation;

class AccommodationListView
{
    /** @var AccommodationView[] */
    public $accommodations;

    /** @var \DateTimeInterface[] */
    public $overnights;

    public function __construct(
        array $accommodations,
        array $overnights
    ) {
        $this->accommodations = $accommodations;
        $this->overnights = $overnights;
    }
}

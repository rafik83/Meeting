<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Rooming\Accommodation;

class OvernightCapacityView
{
    /** @var \DateTimeInterface */
    public $date;

    /** @var int */
    public $total;

    /** @var int */
    public $remaining;

    public function __construct(
        \DateTimeInterface $date,
        int $total = 0,
        int $remaining = 0
    ) {
        $this->date = $date;
        $this->total = $total;
        $this->remaining = $remaining;
    }
}

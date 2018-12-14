<?php

/*
 * This file is part of the PhpStorm project.
 *
 * Copyright (C) PhpStorm
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Rooming\Accommodation;

class AccommodationOvernightCapacityView
{
    /** @var null|\DateTimeInterface */
    public $date;

    /** @var null|int */
    public $capacity;

    public function __construct(
        ?\DateTimeInterface $date = null,
        ?int $capacity = null
    ) {
        $this->date = $date;
        $this->capacity = $capacity;
    }
}

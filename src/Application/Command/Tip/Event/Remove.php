<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Tip\Event;

use Proximum\Vimeet\Domain\Model\Tip\Tip;

class Remove
{
    /** @var Tip */
    public $tip;

    /**
     * @param Tip   $tip
     */
    public function __construct(Tip $tip)
    {
        $this->tip = $tip;
    }
}

<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Happening;

use Proximum\Vimeet\Domain\Model\Happening;

class DatesUpdated
{
    /** @var Happening */
    private $happening;

    /**
     * @param Happening $happening
     */
    public function __construct(Happening $happening)
    {
        $this->happening = $happening;
    }

    /**
     * @return Happening
     */
    public function getHappening(): Happening
    {
        return $this->happening;
    }
}

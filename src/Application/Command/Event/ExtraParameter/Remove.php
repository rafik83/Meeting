<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameter;

use Proximum\Vimeet\Domain\Model\Event\ExtraParameter;

class Remove
{
    /** @var ExtraParameter */
    public $extraParameter;

    /**
     * @param ExtraParameter $extraParameter
     */
    public function __construct(ExtraParameter $extraParameter)
    {
        $this->extraParameter = $extraParameter;
    }
}

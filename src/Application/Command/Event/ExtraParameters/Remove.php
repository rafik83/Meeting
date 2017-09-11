<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\ExtraParameters;

use Proximum\Vimeet\Domain\Model\Event\ExtraParameters;

class Remove
{
    /** @var ExtraParameters */
    public $extraParameters;

    /**
     * @param ExtraParameters $extraParameters
     */
    public function __construct(ExtraParameters $extraParameters)
    {
        $this->extraParameters = $extraParameters;
    }
}

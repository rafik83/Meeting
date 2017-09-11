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

class Update
{
    /** @var ExtraParameters */
    public $extraParameters;

    /** @var string */
    public $name;

    /** @var string */
    public $value;

    /**
     * @param ExtraParameters $extraParameters
     */
    public function __construct(ExtraParameters $extraParameters)
    {
        $this->extraParameters = $extraParameters;
        $this->name = $extraParameters->getName();
        $this->value = $extraParameters->getValue();
    }
}

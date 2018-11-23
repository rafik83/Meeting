<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Exception;

class GivenStepIsRequiredAndNotFilledException extends TemplateException
{
    /** @var int */
    public $step;

    public function __construct(int $step)
    {
        $this->step = $step;

        parent::__construct('template.block.GivenStepIsRequiredAndNotFilled');
    }
}

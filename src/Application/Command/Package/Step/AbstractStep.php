<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Model\Sheet;

class AbstractStep
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var int
     */
    public $currentStep;

    /**
     * @param Sheet $sheet
     * @param int   $currentStep
     */
    public function __construct(Sheet $sheet, $currentStep)
    {
        $this->sheet       = $sheet;
        $this->currentStep = $currentStep;
    }
}

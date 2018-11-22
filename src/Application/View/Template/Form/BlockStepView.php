<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Template\Form;

use Proximum\Vimeet\Domain\Template\Block;

class BlockStepView
{
    /** @var Block */
    public $block;

    /** @var int */
    public $currentStep;

    /** @var int */
    public $totalStep;

    public function __construct(Block $block, int $currentStep, int $totalStep)
    {
        $this->block = $block;
        $this->currentStep = $currentStep;
        $this->totalStep = $totalStep;
    }
}

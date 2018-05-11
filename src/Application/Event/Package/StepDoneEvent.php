<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event\Package;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\Step;
use Symfony\Component\EventDispatcher\Event;

class StepDoneEvent extends Event
{
    /** @var Sheet */
    private $sheet;

    /** @var string */
    private $step;

    public function __construct(Sheet $sheet, string $step)
    {
        if (!\in_array($step, Step::TYPE_STEPS, true)) {
            throw new \InvalidArgumentException('Given step is not valid');
        }

        $this->sheet = $sheet;
        $this->step = $step;
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getStep(): string
    {
        return $this->step;
    }
}

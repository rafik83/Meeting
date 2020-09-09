<?php

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Model\Sheet;

class AbstractStep
{
    /** @var Sheet */
    public $sheet;

    /** @var null|int */
    public $currentStep;

    public function __construct(Sheet $sheet, ?int $currentStep = null)
    {
        $this->sheet = $sheet;
        $this->currentStep = $currentStep;
    }
}

<?php

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;

class AbstractStep implements Command
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

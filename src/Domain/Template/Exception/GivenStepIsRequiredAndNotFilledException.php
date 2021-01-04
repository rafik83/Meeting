<?php

namespace Proximum\Vimeet\Domain\Template\Exception;

class GivenStepIsRequiredAndNotFilledException extends TemplateException
{
    /** @var int */
    public $step;

    public function __construct(int $step)
    {
        $this->step = $step;

        parent::__construct('template.block.givenStepIsRequiredAndNotFilled');
    }
}

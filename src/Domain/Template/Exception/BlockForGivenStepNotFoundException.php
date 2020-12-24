<?php

namespace Proximum\Vimeet\Domain\Template\Exception;

class BlockForGivenStepNotFoundException extends TemplateException
{
    /** @var int */
    public $step;

    public function __construct(int $step)
    {
        $this->step = $step;

        parent::__construct('template.block.blockForGivenStepNotFound', 422);
    }
}

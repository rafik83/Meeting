<?php

namespace Proximum\Vimeet\Application\View\Template\Form;

class StepView
{
    /** @var int */
    public $index;

    /** @var null|string */
    public $title;

    /** @var bool */
    public $accessible;

    public function __construct(
        int $index,
        ?string $title,
        bool $accessible
    ) {
        $this->index = $index;
        $this->title = $title;
        $this->accessible = $accessible;
    }
}

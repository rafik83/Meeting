<?php

namespace Proximum\Vimeet\Domain\Template\Form;

class FormTemplateView
{
    /** @var int */
    public $formTemplateId;

    /** @var string */
    public $title;

    public function __construct(int $formTemplateId, string $title)
    {
        $this->formTemplateId = $formTemplateId;
        $this->title = $title;
    }
}

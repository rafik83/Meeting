<?php

namespace Proximum\Vimeet\Application\View\Template\Form;

class FormTemplateListView
{
    /** @var FormTemplateView[] */
    public $templates;

    public function __construct(array $templates = [])
    {
        $this->templates = $templates;
    }
}

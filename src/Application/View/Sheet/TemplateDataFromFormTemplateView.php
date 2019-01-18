<?php

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Template\TemplateData;

class TemplateDataFromFormTemplateView
{
    /** @var TemplateData */
    public $templateData;

    /** @var FormTemplate */
    public $formTemplate;

    public function __construct(TemplateData $templateData, FormTemplate $formTemplate)
    {
        $this->templateData = $templateData;
        $this->formTemplate = $formTemplate;
    }
}

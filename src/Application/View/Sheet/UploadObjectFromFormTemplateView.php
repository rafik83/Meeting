<?php

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class UploadObjectFromFormTemplateView
{
    /** @var FormTemplate */
    public $formTemplate;

    /** @var TemplateObject\UploadObject */
    public $templateObject;

    public function __construct(FormTemplate $formTemplate, TemplateObject\UploadObject $templateObject)
    {
        $this->formTemplate = $formTemplate;
        $this->templateObject = $templateObject;
    }
}

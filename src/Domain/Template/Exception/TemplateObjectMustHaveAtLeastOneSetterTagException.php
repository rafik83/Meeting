<?php

namespace Proximum\Vimeet\Domain\Template\Exception;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class TemplateObjectMustHaveAtLeastOneSetterTagException extends TemplateException
{
    /** @var TemplateObject[] */
    public $templateObjects = [];

    public function __construct(array $templateObjects)
    {
        $this->templateObjects = $templateObjects;

        parent::__construct('validator.template.templateObjectMustHaveAtLeastOneSetterTag', 422);
    }
}

<?php

namespace Proximum\Vimeet\Domain\Template\Exception;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class RegistrationTemplateObjectMustHaveAtLeastOneSetterTagException extends RegistrationTemplateException
{
    /** @var TemplateObject[] */
    public $templateObjects = [];

    public function __construct(array $templateObjects)
    {
        $this->templateObjects = $templateObjects;
        parent::__construct('template.registration.templateObjectMustHaveAtLeastOneSetterTag', 422);
    }
}

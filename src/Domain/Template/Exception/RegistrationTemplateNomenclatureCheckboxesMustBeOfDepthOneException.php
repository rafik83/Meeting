<?php

namespace Proximum\Vimeet\Domain\Template\Exception;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class RegistrationTemplateNomenclatureCheckboxesMustBeOfDepthOneException extends RegistrationTemplateException
{
    /** @var TemplateObject[] */
    public $templateObjects = [];

    public function __construct(array $templateObjects)
    {
        $this->templateObjects = $templateObjects;

        parent::__construct('template.registration.nomenclatureCheckboxesMustBeOfDepthOne', 422);
    }
}

<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Sheet;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class CreateObjectForm
{
    /** @var TemplateObject */
    public $templateObject;

    /** @var string */
    public $locale;

    /** @var string */
    public $key;

    public function __construct(
        TemplateObject $templateObject,
        string $locale,
        string $key
    ) {
        $this->templateObject = $templateObject;
        $this->locale = $locale;
        $this->key = $key;
    }
}

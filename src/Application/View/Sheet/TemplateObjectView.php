<?php

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Domain\Template\TemplateObject;

class TemplateObjectView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var array
     */
    public $includedProductIds;

    /**
     * @var TemplateObject
     */
    public $templateObject;

    /**
     * TemplateObjectView constructor.
     *
     * @param TemplateObject $templateObject
     * @param string         $label
     */
    public function __construct(
        TemplateObject $templateObject,
        $label
    ) {
        $this->label          = $label;
        $this->templateObject = $templateObject;
    }
}

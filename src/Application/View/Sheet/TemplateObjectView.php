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
     * @param array          $includedProductIds
     */
    public function __construct(
        TemplateObject $templateObject,
        $label,
        array $includedProductIds
    ) {
        $this->label              = $label;
        $this->includedProductIds = $includedProductIds;
        $this->templateObject     = $templateObject;
    }
}

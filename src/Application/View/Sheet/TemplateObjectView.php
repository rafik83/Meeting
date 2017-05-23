<?php

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class TemplateObjectView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $data;

    /**
     * @var Product[]
     */
    public $buyableProducts;

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
     * @param string         $type
     * @param array          $data
     * @param Product[]      $buyableProducts
     * @param array          $includedProductIds
     */
    public function __construct(
        TemplateObject $templateObject,
        $label,
        $type,
        array $data,
        array $buyableProducts,
        array $includedProductIds
    ) {
        $this->label              = $label;
        $this->type               = $type;
        $this->data               = $data;
        $this->buyableProducts    = $buyableProducts;
        $this->includedProductIds = $includedProductIds;
        $this->templateObject     = $templateObject;
    }
}

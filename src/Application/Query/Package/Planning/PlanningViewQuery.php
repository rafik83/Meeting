<?php

namespace Proximum\Vimeet\Application\Query\Package\Planning;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;

class PlanningViewQuery
{
    /**
     * @var string
     */
    public $locale;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Product
     */
    public $product;

    /**
     * @param Sheet   $sheet
     * @param Product $product
     * @param string  $locale
     */
    public function __construct(Sheet $sheet, Product $product, $locale)
    {
        $this->sheet   = $sheet;
        $this->product = $product;
        $this->locale  = $locale;
    }
}

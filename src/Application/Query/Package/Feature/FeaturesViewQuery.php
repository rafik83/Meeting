<?php

namespace Proximum\Vimeet\Application\Query\Package\Feature;

use Proximum\Vimeet\Domain\Model\Product;

class FeaturesViewQuery
{
    /**
     * @var Product
     */
    public $product;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Product $product
     * @param string  $locale
     */
    public function __construct(Product $product, $locale)
    {
        $this->product = $product;
        $this->locale  = $locale;
    }
}

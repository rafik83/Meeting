<?php

namespace Proximum\Vimeet\Application\Command\Product\Remove;

use Proximum\Vimeet\Domain\Model\Product;

class Remove
{
    /** @var Product */
    public $product;

    /**
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}

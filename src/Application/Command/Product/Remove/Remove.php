<?php

namespace Proximum\Vimeet\Application\Command\Product\Remove;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Product;

class Remove implements Command
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

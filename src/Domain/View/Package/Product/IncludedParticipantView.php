<?php

namespace Proximum\Vimeet\Domain\View\Package\Product;

use Proximum\Vimeet\Domain\Model\Product;

class IncludedParticipantView
{
    /** @var Product */
    public $product;

    /** @var int */
    public $totalQuantity;

    /**
     * @param Product $product
     * @param int     $totalQuantity
     */
    public function __construct(Product $product, int $totalQuantity)
    {
        $this->product = $product;
        $this->totalQuantity = $totalQuantity;
    }
}

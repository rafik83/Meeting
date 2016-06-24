<?php


namespace Proximum\Vimeet\Application\Command\Product\Option;


use Proximum\Vimeet\Application\Command\Product\AbstractUpdate;
use Proximum\Vimeet\Domain\Model\Product;

class UpdateOption extends AbstractUpdate
{
    /**
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        parent::__construct($product);
        
        $this->availabilityCurrent = $product->getAvailabilityCurrent();
        $this->availabilityMax = $product->getAvailabilityMax();
        $this->updatable = $product->isUpdatable();
        $this->updatableUntil = $product->getUpdatableUntil();
        $this->buyableUntil = $product->getBuyableUntil();
    }
}

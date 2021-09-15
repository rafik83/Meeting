<?php

namespace Proximum\Vimeet\Domain\Model\Product;

use Proximum\Vimeet\Domain\Model\Product;

/**
 * Product included in the product
 */
class ProductIncluded
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Product
     */
    private $included;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @param Product $product
     * @param Product $included
     * @param int     $quantity
     */
    public function __construct(Product $product, Product $included, $quantity)
    {
        $this->product  = $product;
        $this->included = $included;
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return Product
     */
    public function getIncluded()
    {
        return $this->included;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param $quantity
     *
     * @return ProductIncluded
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }
}

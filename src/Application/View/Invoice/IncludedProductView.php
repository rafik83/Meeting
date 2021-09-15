<?php

namespace Proximum\Vimeet\Application\View\Invoice;

class IncludedProductView
{
    /** @var string */
    public $label;

    /** @var int */
    public $quantity;

    /** @var int in cents */
    public $price;

    /** @var int in cents */
    public $total;

    /**
     * @param string $label
     * @param int    $quantity
     * @param int    $price
     * @param int    $total
     */
    public function __construct($label, $quantity, $price, $total)
    {
        $this->label    = $label;
        $this->quantity = $quantity;
        $this->price    = $price;
        $this->total    = $total;
    }
}

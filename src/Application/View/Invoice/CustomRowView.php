<?php

namespace Proximum\Vimeet\Application\View\Invoice;

class CustomRowView
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
     * @param int    $price
     * @param int    $quantity
     * @param int    $total
     */
    public function __construct($label, $price, $quantity, $total)
    {
        $this->label    = $label;
        $this->price    = $price;
        $this->quantity = $quantity;
        $this->total    = $total;
    }
}

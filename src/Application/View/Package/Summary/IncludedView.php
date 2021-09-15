<?php

namespace Proximum\Vimeet\Application\View\Package\Summary;

class IncludedView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var float
     */
    public $total;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * @param int    $id
     * @param string $title
     * @param int    $quantity
     * @param string $vatMode
     * @param string $currency
     */
    public function __construct(
        $id,
        $title,
        $quantity,
        $vatMode,
        $currency
    ) {
        $this->id        = $id;
        $this->title     = $title;
        $this->quantity  = $quantity;
        $this->total     = 0;
        $this->vatMode   = $vatMode;
        $this->currency  = $currency;
    }
}

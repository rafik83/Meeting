<?php

namespace Proximum\Vimeet\Application\View\Order;

class CustomRowView
{
    /**
     * @var null|int
     */
    public $id;

    /**
     * @var string
     */
    public $label;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var float
     */
    public $price;

    /**
     * @var float
     */
    public $total;

    /** @var string */
    public $vatMode;

    /** @var float */
    public $vatRate;

    /** @var string */
    public $currency;

    public function __construct(
        ?int $id,
        string $label,
        float $price,
        int $quantity,
        string $currency,
        string $vatMode,
        float $vatRate
    ) {
        $this->id = $id;
        $this->label = $label;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->total = $price * $quantity;
        $this->currency = $currency;
        $this->vatMode = $vatMode;
        $this->vatRate = $vatRate;
    }
}

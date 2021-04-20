<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Order\Row;

class UpdateRow implements Command
{
    /**
     * @var Row
     */
    public $row;

    /**
     * @var string
     */
    public $label;

    /**
     * @var float
     */
    public $price;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @param Row $row
     */
    public function __construct(Row $row)
    {
        $this->row      = $row;
        $this->label    = $row->getLabel();
        $this->price    = $row->getPrice();
        $this->quantity = $row->getQuantity();
    }
}

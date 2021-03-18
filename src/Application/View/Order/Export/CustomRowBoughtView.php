<?php

namespace Proximum\Vimeet\Application\View\Order\Export;

class CustomRowBoughtView
{
    /** @var int */
    public $customRowId;

    /** @var float */
    public $unitPrice;

    /** @var int */
    public $quantity;

    /** @var float */
    public $total;

    /** @var string */
    public $title;

    /**
     * @param int    $customRowId
     * @param string $title
     * @param float  $unitPrice
     * @param int    $quantity
     * @param float  $total
     */
    public function __construct(
        $customRowId,
        $title,
        $unitPrice,
        $quantity,
        $total
    ) {
        $this->customRowId = $customRowId;
        $this->title       = $title;
        $this->unitPrice   = $unitPrice;
        $this->quantity    = $quantity;
        $this->total       = $total;
    }

    /**
     * @param int $index
     *
     * @return string
     */
    public function getTitleColumnId($index)
    {
        return sprintf('customRow%sTitle', $index);
    }

    /**
     * @param int $index
     *
     * @return string
     */
    public function getUnitPriceColumnId($index)
    {
        return sprintf('customRow%sUnitPrice', $index);
    }

    /**
     * @param int $index
     *
     * @return string
     */
    public function getQuantityColumnId($index)
    {
        return sprintf('customRow%sQuantity', $index);
    }

    /**
     * @param int $index
     *
     * @return string
     */
    public function getTotalColumnId($index)
    {
        return sprintf('customRow%sTotal', $index);
    }
}

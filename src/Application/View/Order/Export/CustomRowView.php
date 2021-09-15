<?php

namespace Proximum\Vimeet\Application\View\Order\Export;

class CustomRowView
{
    /** @var string */
    public $title;

    /** @var string */
    public $unitPriceTitle;

    /** @var string */
    public $quantityTitle;

    /** @var string */
    public $totalTitle;

    /** @var int */
    public $index;

    /**
     * @param int    $index
     * @param string $title
     * @param string $unitPriceTitle
     * @param string $quantityTitle
     * @param string $totalTitle
     */
    public function __construct($index, $title, $unitPriceTitle, $quantityTitle, $totalTitle)
    {
        $this->index          = $index;
        $this->title          = $title;
        $this->unitPriceTitle = $unitPriceTitle;
        $this->quantityTitle  = $quantityTitle;
        $this->totalTitle     = $totalTitle;
    }

    /**
     * @return string
     */
    public function getTitleColumnId()
    {
        return sprintf('customRow%sTitle', $this->index);
    }

    /**
     * @return string
     */
    public function getUnitPriceColumnId()
    {
        return sprintf('customRow%sUnitPrice', $this->index);
    }

    /**
     * @return string
     */
    public function getQuantityColumnId()
    {
        return sprintf('customRow%sQuantity', $this->index);
    }

    /**
     * @return string
     */
    public function getTotalColumnId()
    {
        return sprintf('customRow%sTotal', $this->index);
    }
}

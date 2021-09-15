<?php

namespace Proximum\Vimeet\Application\View\Order;

class CustomRowsView
{
    /**
     * @var CustomRowView[]
     */
    public $customRows = [];

    /**
     * @param CustomRowView $customRow
     */
    public function addCustomRow(CustomRowView $customRow)
    {
        $this->customRows[] = $customRow;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return array_reduce($this->customRows, function ($carry, CustomRowView $customRowView) {
            return $carry + $customRowView->total;
        }, 0);
    }
}

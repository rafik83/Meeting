<?php

namespace Proximum\Vimeet\Application\View\Order;

use Proximum\Vimeet\Domain\Model\Sheet;

class GroupView
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var int
     */
    public $groupId;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $type;

    /**
     * @var RowView[]
     */
    public $products = [];

    /**
     * @var array
     */
    public $customRows = [];

    /**
     * @var null|int
     */
    public $stepIndex;

    /**
     * @param Sheet    $sheet
     * @param string   $label
     * @param string   $type
     * @param int      $groupId
     * @param array    $products
     * @param array    $customRows
     * @param null|int $stepIndex
     */
    public function __construct(
        Sheet $sheet,
        $label,
        $type,
        $groupId,
        array $products = [],
        array  $customRows = [],
        $stepIndex = null
    ) {
        $this->sheet      = $sheet;
        $this->label      = $label;
        $this->type       = $type;
        $this->groupId    = $groupId;
        $this->products   = $products;
        $this->customRows = $customRows;
        $this->stepIndex  = $stepIndex;
    }

    /**
     * @param RowView $product
     */
    public function addProduct(RowView $product)
    {
        $this->products[] = $product;
    }

    /**
     * @param CustomRowView $customRow
     */
    public function addCustomRow(CustomRowView $customRow)
    {
        $this->customRows[] = $customRow;
    }

    public function hasProductOrCustomRow(): bool
    {
        return 0 < count($this->products) || 0 < count($this->customRows);
    }
}

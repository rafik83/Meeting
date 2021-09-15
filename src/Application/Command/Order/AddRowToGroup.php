<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Model\Order;

class AddRowToGroup extends AbstractAddRow
{
    /** @var int */
    public $groupId;

    /**
     * @param Order $order
     * @param int   $groupId
     */
    public function __construct(Order $order, $groupId)
    {
        $this->order   = $order;
        $this->groupId = $groupId;
    }
}

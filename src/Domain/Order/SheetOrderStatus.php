<?php


namespace Proximum\Vimeet\Domain\Order;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;

class SheetOrderStatus
{
    /** @var Balance */
    private $balance;

    public function __construct(Balance $balance)
    {
        $this->balance = $balance;
    }

    public function getStatus(Sheet $sheet): string
    {
        $orderVatViews = $this->balance->getNotCancelledOrderVatViews($sheet);

        if (empty($orderVatViews)) {
            return Constant::ORDER_STATUS_NO_ORDER;
        }

        $totalWithoutVat = $this->balance->getTotalWithoutVat($sheet);

        if ($totalWithoutVat > 0) {
            return Constant::ORDER_STATUS_TOTAL_ORDER_SUPERIOR_ZERO;
        }

        return Constant::ORDER_STATUS_TOTAL_ORDER_EQUAL_ZERO;
    }
}

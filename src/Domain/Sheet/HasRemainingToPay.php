<?php

namespace Proximum\Vimeet\Domain\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Balance;

class HasRemainingToPay
{
    /** @var Balance */
    private $balance;

    public function __construct(Balance $balance)
    {
        $this->balance = $balance;
    }

    public function isSatisfiedBy(Sheet $sheet): bool
    {
        if (!$sheet->getPackage()->isPassable()) {
            return false;
        }

        if (!$sheet->hasNotCancelledOrders()) {
            return true;
        }

        return 0 !== $this->balance->getRemainingToPay($sheet);
    }
}

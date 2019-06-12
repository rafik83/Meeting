<?php

namespace Proximum\Vimeet\Application\Query\Tip\Condition;

use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Order\SheetOrderStatus;

class OrderCondition implements ConditionInterface
{
    /** @var SheetOrderStatus */
    private $sheetOrderStatus;

    public function __construct(SheetOrderStatus $sheetOrderStatus)
    {
        $this->sheetOrderStatus = $sheetOrderStatus;
    }

    public function isSatisfiedBy(TipTranslationViewQuery $query, TipTranslationView $tipTranslationView): bool
    {
        if (null === $tipTranslationView->conditionOnOrders || empty($tipTranslationView->conditionOnOrders)) {
            return true;
        }

        $sheetOrderStatus = $this->sheetOrderStatus->getStatus($query->sheet);

        if (Constant::NO_ORDER === $sheetOrderStatus
            && in_array(Tip::CONDITION_ON_ORDERS_WITHOUT, $tipTranslationView->conditionOnOrders, true)
        ) {
            return true;
        }

        if (Constant::ORDER_STATUS_TOTAL_ORDER_EQUAL_ZERO === $sheetOrderStatus
            && in_array(Tip::CONDITION_ON_ORDERS_TOTAL_EQUAL_ZERO, $tipTranslationView->conditionOnOrders, true)
        ) {
            return true;
        }

        if (Constant::ORDER_STATUS_TOTAL_ORDER_SUPERIOR_ZERO === $sheetOrderStatus
            && in_array(Tip::CONDITION_ON_ORDERS_TOTAL_SUPERIOR_ZERO, $tipTranslationView->conditionOnOrders, true)
        ) {
            return true;
        }

        return false;
    }
}

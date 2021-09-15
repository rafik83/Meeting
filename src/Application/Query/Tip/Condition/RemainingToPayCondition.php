<?php

namespace Proximum\Vimeet\Application\Query\Tip\Condition;

use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Proximum\Vimeet\Domain\Order\Balance;

class RemainingToPayCondition implements ConditionInterface
{
    /** @var Balance */
    private $balance;

    public function __construct(Balance $balance)
    {
        $this->balance = $balance;
    }

    public function isSatisfiedBy(TipTranslationViewQuery $query, TipTranslationView $tipTranslationView): bool
    {
        if (null === $tipTranslationView->conditionHasRemainingToPay) {
            return true;
        }

        $hasRemainingToPay = $this->balance->getRemainingToPay($query->sheet) > 0;

        return $hasRemainingToPay === $tipTranslationView->conditionHasRemainingToPay;
    }
}

<?php

namespace Proximum\Vimeet\Application\Query\Tip\Condition;

use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;

class SheetCompletionCondition implements ConditionInterface
{
    public function isSatisfiedBy(TipTranslationViewQuery $query, TipTranslationView $tipTranslationView): bool
    {
        if (null === $tipTranslationView->conditionIsCompleteSheet) {
            return true;
        }

        return $query->sheet->isCompleted() === $tipTranslationView->conditionIsCompleteSheet;
    }
}

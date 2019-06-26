<?php

namespace Proximum\Vimeet\Application\Query\Tip\Condition;

use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;

interface ConditionInterface
{
    public function isSatisfiedBy(TipTranslationViewQuery $query, TipTranslationView $tipTranslationView): bool;
}

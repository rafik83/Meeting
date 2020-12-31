<?php

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer;

use Proximum\Vimeet\Domain\ConditionRules\View\Condition;

interface ConditionRulesTransformerInterface
{
    public function transform(Condition $condition): array;
}

<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules\View;

use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperator;

class Condition implements Rule
{
    /** @var LogicalOperator */
    private $logicalOperator;

    /** @var array */
    private $rules;

    /**
     * @param Rule[] $rules
     */
    public function __construct(LogicalOperator $logicalOperator, array $rules)
    {
        $this->logicalOperator = $logicalOperator;
        $this->rules = $rules;
    }

    public function getLogicalOperator(): LogicalOperator
    {
        return $this->logicalOperator;
    }

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}

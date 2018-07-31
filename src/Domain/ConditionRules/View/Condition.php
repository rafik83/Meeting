<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules\View;

use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorInterface;

class Condition implements RuleInterface
{
    /** @var LogicalOperatorInterface */
    private $logicalOperator;

    /** @var RuleInterface[] */
    private $rules;

    public function __construct(LogicalOperatorInterface $logicalOperator, array $rules)
    {
        $this->logicalOperator = $logicalOperator;
        $this->rules = $rules;
    }

    public function getLogicalOperator(): LogicalOperatorInterface
    {
        return $this->logicalOperator;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}

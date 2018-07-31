<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules\View;

use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorInterface;

class Field implements RuleInterface
{
    /** @var string */
    private $field;

    /** @var ComparisonOperatorInterface */
    private $comparisonOperator;

    /** @var string|array */
    private $value;

    public function __construct(string $field, ComparisonOperatorInterface $comparisonOperator, $value)
    {
        $this->field = $field;
        $this->comparisonOperator = $comparisonOperator;
        $this->value = $value;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getComparisonOperator(): ComparisonOperatorInterface
    {
        return $this->comparisonOperator;
    }

    public function getValue()
    {
        return $this->value;
    }
}

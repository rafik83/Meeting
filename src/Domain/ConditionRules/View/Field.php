<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules\View;

use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperator;

class Field implements Rule
{
    /** @var string */
    private $field;

    /** @var ComparisonOperator */
    private $comparisonOperator;

    /** @var mixed */
    private $value;

    /**
     * @var string|array $value
     */
    public function __construct(string $field, ComparisonOperator $comparisonOperator, $value)
    {
        $this->field = $field;
        $this->comparisonOperator = $comparisonOperator;
        $this->value = $value;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getComparisonOperator(): ComparisonOperator
    {
        return $this->comparisonOperator;
    }

    public function getValue()
    {
        return $this->value;
    }
}

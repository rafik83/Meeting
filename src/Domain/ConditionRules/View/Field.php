<?php

namespace Proximum\Vimeet\Domain\ConditionRules\View;

use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorInterface;

class Field implements RuleInterface
{
    /** @var string */
    private $field;

    /** @var ComparisonOperatorInterface */
    private $comparisonOperator;

    /** @var string */
    private $input;

    /** @var string|array|null */
    private $value;

    /** @var string */
    private $locale;

    public function __construct(
        string $field,
        ComparisonOperatorInterface $comparisonOperator,
        string $input,
        $value,
        string $locale
    ) {
        $this->field = $field;
        $this->comparisonOperator = $comparisonOperator;
        $this->input = $input;
        $this->value = $value;
        $this->locale = $locale;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getComparisonOperator(): ComparisonOperatorInterface
    {
        return $this->comparisonOperator;
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}

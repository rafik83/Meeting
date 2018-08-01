<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorBeginsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEndsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotBeginsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotEndsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class TextTransformer implements InputTransformerInterface
{
    public static function transform(Field $field): array
    {
        if (!self::supports($field)) {
            return [];
        }

        $query = [
            'query_string' => [
                'default_field' => $field->getField(),
                'query' => self::getFilterQuery($field),
            ],
        ];

        if (self::isContraryComparisonOperator($field)) {
            $query = [
                'bool' => [
                    'must_not' => $query,
                ],
            ];
        }

        return $query;
    }

    private static function isContraryComparisonOperator(Field $field): bool
    {
        switch (\get_class($field->getComparisonOperator())) {
            case ComparisonOperatorNotEndsWith::class:
            case ComparisonOperatorNotBeginsWith::class:
            case ComparisonOperatorNotContains::class:
            case ComparisonOperatorNotEqual::class:
                return true;
            default:
                return false;
        }
    }

    private static function getFilterQuery(Field $field): ?string
    {
        switch (\get_class($field->getComparisonOperator())) {
            case ComparisonOperatorEndsWith::class:
            case ComparisonOperatorNotEndsWith::class:
                return sprintf('*%s', $field->getValue());
            case ComparisonOperatorBeginsWith::class:
            case ComparisonOperatorNotBeginsWith::class:
                return sprintf('%s*', $field->getValue());
            case ComparisonOperatorContains::class:
            case ComparisonOperatorNotContains::class:
                return sprintf('*%s*', $field->getValue());
            default:
                return $field->getValue();
        }
    }

    public static function supports(Field $field): bool
    {
        return 'text' === $field->getInput();
    }
}

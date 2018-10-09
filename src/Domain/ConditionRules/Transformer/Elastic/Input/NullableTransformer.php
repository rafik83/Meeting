<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\NestedQueryTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\QueryKeyTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotNull;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNull;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class NullableTransformer implements InputTransformerInterface
{
    public static function transform(Field $field): array
    {
        if (!self::supports($field)) {
            return [];
        }

        $operator = self::isContraryComparisonOperator($field) ? 'must' : 'must_not';

        $query = [
            'constant_score' => [
                'filter' => [
                    'bool' => [
                        $operator => [
                            'exists' => [
                                'field' => QueryKeyTransformer::getQueryKey($field),
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $query = NestedQueryTransformer::transformIfNeeded($field, $query);

        return $query;
    }

    public static function supports(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonOperatorNotNull ||
            $field->getComparisonOperator() instanceof ComparisonOperatorNull;
    }

    private static function isContraryComparisonOperator(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonOperatorNotNull;
    }
}

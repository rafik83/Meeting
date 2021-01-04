<?php

namespace Proximum\Vimeet\Domain\ConditionRules;

final class ComparisonOperatorsByType
{
    public const OPERATORS = [
        'string' => [
            'contains',
            'not_contains',
            'equal',
            'not_equal',
            'is_null',
            'is_not_null',
            'begins_with',
            'not_begins_with',
            'ends_with',
            'not_ends_with',
        ],
        'boolean' => [
            'equal',
        ],
        'nomenclature' => [
            'in',
            'not_in',
            'is_null',
            'is_not_null'
        ],
        'message' => [
            'in',
            'not_in'
        ],
        'participation_type' => [
            'in',
            'not_in',
        ],
        'keywords' => [
            'contains'
        ]
    ];
}

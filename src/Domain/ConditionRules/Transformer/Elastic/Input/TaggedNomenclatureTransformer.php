<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class TaggedNomenclatureTransformer implements InputTransformerInterface
{
    public static function transform(Field $field): array
    {
        if (!self::supports($field)) {
            return [];
        }

        $mappingKey = TypesMapping::SEARCH_MAPPING[TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE]['path'];
        $values = (array) $field->getValue();
        $query = [
            'nested' => [
                'path' => TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE,
                'query' => []
            ]
        ];

        foreach ($values as $value) {
            $query['nested']['query']['bool']['should'][] = [
                'term' => [
                    $mappingKey => [
                        'value' => $value,
                    ],
                ],
            ];
        }

        if (self::isContraryComparisonOperator($field)) {
            $query = [
                'bool' => [
                    'must_not' => $query,
                ],
            ];
        }

        return $query;
    }

    public static function supports(Field $field): bool
    {
        return ($field->getComparisonOperator() instanceof ComparisonOperatorIn
            || $field->getComparisonOperator() instanceof ComparisonOperatorNotIn)
            && false !== stripos($field->getField(), TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE);
    }

    private static function isContraryComparisonOperator(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonOperatorNotIn;
    }
}

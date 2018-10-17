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

        $query = [
            'bool' => [
                'must' => [
                    [
                        'nested' => [
                            'path' => TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE,
                        ],
                    ],
                    [
                        'nested' => [
                            'path' => TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE.'.values',
                        ],
                    ],
                ],
            ],
        ];

        // todo: retrieve real tags
        $tags = ['sheet_test', 'sheet_test2'];

        $query = self::buildTagQuery($query, $tags);
        $query = self::buildKeysQuery($query, (array) $field->getValue());

        if (self::isContraryComparisonOperator($field)) {
            $query = [
                'bool' => [
                    'must_not' => $query,
                ],
            ];
        }

        return $query;
    }

    private static function buildTagQuery(array $query, array $tags): array
    {
        $tagMappingPath = TypesMapping::SEARCH_MAPPING[TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE]['rules']['tag']['path'];

        foreach ($tags as $tag) {
            $query['bool']['must'][0]['nested']['query']['bool']['should'][] = [
                'term' => [
                    $tagMappingPath => [
                        'value' => $tag,
                    ],
                ],
            ];
        }

        return $query;
    }

    private static function buildKeysQuery(array $query, array $keys): array
    {
        $keyMappingPath = TypesMapping::SEARCH_MAPPING[TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE]['rules']['key']['path'];

        foreach ($keys as $key) {
            $query['bool']['must'][1]['nested']['query']['bool']['should'][] = [
                'term' => [
                    $keyMappingPath => [
                        'value' => $key,
                    ],
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

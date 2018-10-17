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
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class TaggedNomenclatureTransformer implements InputTransformerInterface
{
    public static function transform(Field $field): array
    {
        if (!self::supports($field)) {
            return [];
        }

        // todo: retrieve real tags
        $tags = ['sheet_test', 'sheet_test2'];

        $query = [
            'bool' => [
                'must' => [
                    self::buildTagQuery($tags),
                    self::buildKeysQuery((array) $field->getValue()),
                ]
            ]
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

    private static function buildTagQuery(array $tags): array
    {
        $tagMappingPath = TypesMapping::SEARCH_MAPPING[TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE]['rules']['tag']['path'];
        $query = [];

        foreach ($tags as $tag) {
            $query['bool']['should'][] = [
                'term' => [
                    $tagMappingPath => [
                        'value' => $tag,
                    ],
                ],
            ];
        }

        return [
            'nested' => [
                'path' => TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE,
                'query' => $query,
            ],
        ];
    }

    private static function buildKeysQuery(array $keys): array
    {
        $keyMappingPath = TypesMapping::SEARCH_MAPPING[TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE]['rules']['key']['path'];
        $query = [];

        foreach ($keys as $key) {
            $query['bool']['should'][] = [
                'term' => [
                    $keyMappingPath => [
                        'value' => $key,
                    ],
                ],
            ];
        }

        return [
            'nested' => [
                'path' => sprintf('%s.values', TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE),
                'query' => $query,
            ],
        ];
    }

    public static function supports(Field $field): bool
    {
        return false !== stripos($field->getField(), TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE);
    }

    private static function isContraryComparisonOperator(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonOperatorNotIn;
    }
}

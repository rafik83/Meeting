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
    public function transform(Field $field): array
    {
        if (!$this->supports($field)) {
            return [];
        }

        // todo: retrieve real tags
        $tags = ['sheet_test', 'sheet_test2'];

        $query = [
            $this->buildTagQuery($tags),
            $this->buildKeysQuery((array) $field->getValue()),
        ];

        if ($this->isContraryComparisonOperator($field)) {
            $query = [
                'bool' => [
                    'must_not' => $query,
                ],
            ];
        }

        return $query;
    }

    private function buildTagQuery(array $tags): array
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

    private function buildKeysQuery(array $keys): array
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

    public function supports(Field $field): bool
    {
        return false !== stripos($field->getField(), TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE);
    }

    private function isContraryComparisonOperator(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonOperatorNotIn;
    }
}

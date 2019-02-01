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
use Proximum\Vimeet\Domain\ConditionRules\GetSheetIdsByParticipationTypeIds;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotIn;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotNull;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNull;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class ParticipationTypeTransformer implements InputTransformerInterface
{
    /** @var GetSheetIdsByParticipationTypeIds */
    private $getSheetIdsByParticipationTypeIds;

    public function __construct(GetSheetIdsByParticipationTypeIds $getSheetIdsByParticipationTypeIds)
    {
        $this->getSheetIdsByParticipationTypeIds = $getSheetIdsByParticipationTypeIds;
    }

    public function transform(Field $field): array
    {
        if (!$this->supports($field)) {
            return [];
        }

        $sheetIds = ($this->getSheetIdsByParticipationTypeIds)($field->getValue());

        if (empty($sheetIds)) {
            return [];
        }

        // Not null or null query
        if ($this->isNullableComparisonOperator($field)) {
            return [
                $this->buildNestedNullableQuery($field, $tags)
            ];
        }

        return [
            $this->buildNestedTagQuery($tags),
            $this->buildNestedKeysQuery($field, (array) $field->getValue()),
        ];
    }

    private function buildNestedNullableQuery(Field $field, array $tags): array
    {
        $operator = $field->getComparisonOperator() instanceof ComparisonOperatorNull ? 'must_not' : 'must';

        return [
            'bool' => [
                $operator => [
                    $this->buildNestedTagQuery($tags),
                ],
            ],
        ];
    }

    private function buildNestedTagQuery(array $tags): array
    {
        $query = [];

        foreach ($tags as $tag) {
            $query['bool']['must'][] = [
                'term' => [
                    $this->getTagSearchMapping() => [
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

    private function buildNestedKeysQuery(Field $field, array $keys): array
    {
        $nestedQuery = [];

        foreach ($keys as $key) {
            $nestedQuery['bool']['should'][] = [
                'term' => [
                    $this->getKeysSearchMapping() => [
                        'value' => $key,
                    ],
                ],
            ];
        }

        $query = [
            'nested' => [
                'path' => sprintf('%s.values', TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE),
                'query' => $nestedQuery,
            ],
        ];

        if ($this->isContraryComparisonOperator($field)) {
            return [
                'bool' => [
                    'must_not' => $query,
                ],
            ];
        }

        return $query;
    }

    private function getKeysSearchMapping(): string
    {
        return TypesMapping::SEARCH_MAPPING[TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE]['rules']['key']['path'];
    }

    private function getTagSearchMapping(): string
    {
        return TypesMapping::SEARCH_MAPPING[TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE]['rules']['tag']['path'];
    }

    public function supports(Field $field): bool
    {

        return $field->getField() === 'participation_type';
    }

    private function isContraryComparisonOperator(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonOperatorNotIn
            || $field->getComparisonOperator() instanceof ComparisonOperatorNotNull;
    }

    private function isNullableComparisonOperator(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonOperatorNull
            || $field->getComparisonOperator() instanceof ComparisonOperatorNotNull;
    }
}

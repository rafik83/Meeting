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
use Proximum\Vimeet\Domain\ConditionRules\GetSheetIdsByFilters;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\Model\Event;

class ParticipationTypeTransformer implements InputTransformerInterface
{
    /** @var GetSheetIdsByFilters */
    private $getSheetIdsByFilters;

    /** @var Event */
    private $event;

    /** @var string */
    private $locale;

    public function __construct(GetSheetIdsByFilters $getSheetIdsByFilters)
    {
        $this->getSheetIdsByFilters = $getSheetIdsByFilters;
    }

    public function setEventAndLocale(Event $event, string $locale): void
    {
        $this->event = $event;
        $this->locale = $locale;
    }

    public function transform(Field $field): array
    {
        if (!$this->supports($field)) {
            return [];
        }

        $sheetIds = ($this->getSheetIdsByFilters)(
            $this->event,
            $this->locale,
            ['type' => $field->getValue()]
        );

        if (empty($sheetIds)) {
            return [];
        }

        $query = $this->buildNestedSheetIdsQuery($sheetIds);

        if ($this->isContraryComparisonOperator($field)) {
            return [
                'bool' => [
                    'must_not' => $query,
                ],
            ];
        }

        return $query;
    }

    private function buildNestedSheetIdsQuery(array $sheetIds): array
    {
        $query = [];

        foreach ($sheetIds as $sheetId) {
            $query['bool']['should'][] = [
                'match' => [
                    sprintf('%s.id', TypesMapping::USER_EVENT_VIEW_SHEETS) => $sheetId,
                ],
            ];
        }

        return [
            'nested' => [
                'path' => TypesMapping::USER_EVENT_VIEW_SHEETS,
                'query' => $query,
            ],
        ];
    }

    public function supports(Field $field): bool
    {
        return 'participation_type' === $field->getField();
    }

    private function isContraryComparisonOperator(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonOperatorNotIn;
    }
}

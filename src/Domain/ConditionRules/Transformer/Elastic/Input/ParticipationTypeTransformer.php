<?php

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
            return $this->emptySheetsQuery($field);
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

    /**
     * In the case there is no sheet attached to the type,
     * we filter on a non-existent sheet id to go 0 result
     *
     * @return array
     */
    private function emptySheetsQuery(Field $field): array
    {
        $operator = $this->isContraryComparisonOperator($field) ? 'must_not' : 'must';

        return [
            'constant_score' => [
                'filter' => [
                    'bool' => [
                        $operator => [
                            'exists' => [
                                'field' => 'sheets',
                            ],
                        ],
                    ],
                ],
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

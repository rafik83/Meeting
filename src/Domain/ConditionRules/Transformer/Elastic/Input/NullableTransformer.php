<?php

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\NestedQueryTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\QueryKeyTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotNull;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNull;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class NullableTransformer implements InputTransformerInterface
{
    public function transform(Field $field): array
    {
        if (!$this->supports($field)) {
            return [];
        }

        $operator = $this->isContraryComparisonOperator($field) ? 'must' : 'must_not';

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

    public function supports(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonOperatorNotNull ||
            $field->getComparisonOperator() instanceof ComparisonOperatorNull;
    }

    private function isContraryComparisonOperator(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonOperatorNotNull;
    }
}

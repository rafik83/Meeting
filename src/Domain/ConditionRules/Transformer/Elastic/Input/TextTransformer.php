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
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonContraryOperatorInterface;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorBeginsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEndsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotBeginsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotEndsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class TextTransformer implements InputTransformerInterface
{
    public const CLEAN_SEARCH_FIELD_REGEX = '/[\+\-\/=&|:\?!^(){}\[\]><"~]/';

    public function transform(Field $field): array
    {
        if (!$this->supports($field)) {
            return [];
        }

        $query = $this->getQuery($field);
        $query = NestedQueryTransformer::transformIfNeeded($field, $query);

        if ($this->isContraryComparisonOperator($field)) {
            $query = [
                'bool' => [
                    'must_not' => $query,
                ],
            ];
        }

        return $query;
    }

    private function getQuery(Field $field): array
    {
        if ($field->getComparisonOperator() instanceof ComparisonOperatorEqual
            || $field->getComparisonOperator() instanceof ComparisonOperatorNotEqual
        ) {
            return [
                'match' => [
                    QueryKeyTransformer::getQueryKey($field) => $this->getFilterQuery($field),
                ],
            ];
        }

        return [
            'query_string' => [
                'default_field' => QueryKeyTransformer::getQueryKey($field),
                'query' => $this->getFilterQuery($field),
                'default_operator' => 'AND',
            ],
        ];
    }

    private function isContraryComparisonOperator(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonContraryOperatorInterface;
    }

    private function getFilterQuery(Field $field): ?string
    {
        $cleanValue = preg_replace(self::CLEAN_SEARCH_FIELD_REGEX, ' ', $field->getValue());

        switch (\get_class($field->getComparisonOperator())) {
            case ComparisonOperatorEndsWith::class:
            case ComparisonOperatorNotEndsWith::class:
                return sprintf('*%s', $cleanValue);
            case ComparisonOperatorBeginsWith::class:
            case ComparisonOperatorNotBeginsWith::class:
                return sprintf('%s*', $cleanValue);
            case ComparisonOperatorContains::class:
            case ComparisonOperatorNotContains::class:
                return sprintf('*%s*', $cleanValue);
            default:
                return $field->getValue();
        }
    }

    public function supports(Field $field): bool
    {
        return 'text' === $field->getInput();
    }
}

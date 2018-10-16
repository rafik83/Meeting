<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic;

use Proximum\Vimeet\Domain\ConditionRules\Transformer\ConditionRulesTransformerInterface;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TaggedNomenclatureTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\NullableTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\RadioTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TextTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorAnd;
use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;

class ConditionRulesToElasticTransformer implements ConditionRulesTransformerInterface
{
    public function transform(Condition $condition): array
    {
        $queries = [];
        $operator = $this->getOperator($condition);

        foreach ($condition->getRules() as $rule) {
            $queries['bool'][$operator][] = $this->buildQueries($rule);
        }

        return $queries;
    }

    private function buildQueries(RuleInterface $rule): array
    {
        if ($rule instanceof Condition) {
            $subQueries = [];
            $operator = $this->getOperator($rule);

            foreach ($rule->getRules() as $subRule) {
                $subQueries['bool'][$operator][] = $this->buildQueries($subRule);
            }

            return $subQueries;
        }

        /** @var Field $field */
        $field = $rule;

        return $this->buildFieldQuery($field);
    }

    private function buildFieldQuery(Field $field): array
    {
        if (NullableTransformer::supports($field)) {
            return NullableTransformer::transform($field);
        }

        if (TextTransformer::supports($field)) {
            return TextTransformer::transform($field);
        }

        if (RadioTransformer::supports($field)) {
            return RadioTransformer::transform($field);
        }

        if (TaggedNomenclatureTransformer::supports($field)) {
            return TaggedNomenclatureTransformer::transform($field);
        }

        return [];
    }

    private function getOperator(Condition $condition): string
    {
        return $condition->getLogicalOperator() instanceof LogicalOperatorAnd ? 'must' : 'should';
    }
}

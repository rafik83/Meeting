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
    /** @var NullableTransformer */
    private $nullableTransformer;

    /** @var RadioTransformer */
    private $radioTransformer;

    /** @var TaggedNomenclatureTransformer */
    private $taggedNomenclatureTransformer;

    /** @var TextTransformer */
    private $textTransformer;

    public function __construct(
        NullableTransformer $nullableTransformer,
        RadioTransformer $radioTransformer,
        TaggedNomenclatureTransformer $taggedNomenclatureTransformer,
        TextTransformer $textTransformer
    ) {
        $this->nullableTransformer = $nullableTransformer;
        $this->radioTransformer = $radioTransformer;
        $this->taggedNomenclatureTransformer = $taggedNomenclatureTransformer;
        $this->textTransformer = $textTransformer;
    }

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
        if ($this->nullableTransformer->supports($field)) {
            return $this->nullableTransformer->transform($field);
        }

        if ($this->textTransformer->supports($field)) {
            return $this->textTransformer->transform($field);
        }

        if ($this->radioTransformer->supports($field)) {
            return $this->radioTransformer->transform($field);
        }

        if ($this->taggedNomenclatureTransformer->supports($field)) {
            return $this->taggedNomenclatureTransformer->transform($field);
        }

        return [];
    }

    private function getOperator(Condition $condition): string
    {
        return $condition->getLogicalOperator() instanceof LogicalOperatorAnd ? 'must' : 'should';
    }
}

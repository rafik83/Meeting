<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules;

use Proximum\Vimeet\Domain\ConditionRules\Exceptions\ComparisonNotFoundException;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorInterface;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotIn;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotNull;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNull;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorAnd;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorInterface;
use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorOr;
use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;

class ConditionRulesParser
{
    public const LOGICAL_OPERATOR_KEY = 'logicalOperator';
    public const RULES_KEY = 'rules';

    public static function parse(array $condition): RuleInterface
    {
        $buildRules = [];

        foreach ($condition[self::RULES_KEY] as $rule) {
            $buildRules[] = self::rulesBuilder($buildRules, $rule);
        }

        return new Condition(self::getLogicalOperator($condition), $buildRules);
    }

    private static function rulesBuilder(array $buildRules, array $initialRule): RuleInterface
    {
        if (isset($initialRule[self::RULES_KEY])) {
            $subBuildRules = [];

            foreach ($initialRule[self::RULES_KEY] as $subInitialRule) {
                $subBuildRules[] = self::rulesBuilder($buildRules, $subInitialRule);
            }

            return new Condition(self::getLogicalOperator($initialRule), $subBuildRules);
        }

        return new Field(
            $initialRule['field'],
            self::getComparisonOperator($initialRule['comparisonOperator']),
            $initialRule['value']
        );
    }

    private static function getLogicalOperator(array $rule): LogicalOperatorInterface
    {
        return $rule[self::LOGICAL_OPERATOR_KEY] === 'AND'
            ? new LogicalOperatorAnd()
            : new LogicalOperatorOr();
    }

    private static function getComparisonOperator(string $comparisonOperator): ComparisonOperatorInterface
    {
        switch ($comparisonOperator) {
            case 'equal':
                return new ComparisonOperatorEqual();
            case 'not_equal':
                return new ComparisonOperatorNotEqual();
            case 'in':
                return new ComparisonOperatorIn();
            case 'not_in':
                return new ComparisonOperatorNotIn();
            case 'is_null':
                return new ComparisonOperatorNull();
            case 'is_not_null':
                return new ComparisonOperatorNotNull();
            default:
                throw new ComparisonNotFoundException();
        }
    }
}

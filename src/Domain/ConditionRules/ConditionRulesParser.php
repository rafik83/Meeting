<?php

namespace Proximum\Vimeet\Domain\ConditionRules;

use Proximum\Vimeet\Domain\ConditionRules\Exceptions\ComparisonNotFoundException;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorBeginsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEndsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorInterface;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotBeginsWith;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotEndsWith;
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
use Proximum\Vimeet\Domain\Model\Event;

class ConditionRulesParser
{
    public static function parse(Event $event, string $locale, array $condition): RuleInterface
    {
        $buildRules = [];

        foreach ($condition['rules'] as $rule) {
            $buildRules[] = self::rulesBuilder($event, $locale, $rule);
        }

        return new Condition(
            $event,
            $locale,
            self::getLogicalOperator($condition),
            $buildRules
        );
    }

    private static function rulesBuilder(Event $event, string $locale, array $initialRule): RuleInterface
    {
        if (isset($initialRule['rules'])) {
            $subBuildRules = [];

            foreach ($initialRule['rules'] as $subInitialRule) {
                $subBuildRules[] = self::rulesBuilder($event, $locale, $subInitialRule);
            }

            return new Condition(
                $event,
                $locale,
                self::getLogicalOperator($initialRule),
                $subBuildRules
            );
        }

        return new Field(
            $initialRule['field'],
            self::getComparisonOperator($initialRule['operator']),
            $initialRule['input'],
            $initialRule['value'],
            $locale
        );
    }

    private static function getLogicalOperator(array $rule): LogicalOperatorInterface
    {
        return 'AND' === $rule['condition']
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
            case 'contains':
                return new ComparisonOperatorContains();
            case 'not_contains':
                return new ComparisonOperatorNotContains();
            case 'is_null':
                return new ComparisonOperatorNull();
            case 'is_not_null':
                return new ComparisonOperatorNotNull();
            case 'begins_with':
                return new ComparisonOperatorBeginsWith();
            case 'not_begins_with':
                return new ComparisonOperatorNotBeginsWith();
            case 'ends_with':
                return new ComparisonOperatorEndsWith();
            case 'not_ends_with':
                return new ComparisonOperatorNotEndsWith();
            default:
                throw new ComparisonNotFoundException();
        }
    }
}

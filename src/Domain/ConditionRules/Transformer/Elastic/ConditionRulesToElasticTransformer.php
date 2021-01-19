<?php

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic;

use Proximum\Vimeet\Domain\ConditionRules\Transformer\ConditionRulesTransformerInterface;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\KeywordTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\MessageTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\ParticipationTypeTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TaggedNomenclatureTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\NullableTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\RadioTransformer;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\TemplateObjectFilterTransformer;
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

    /** @var ParticipationTypeTransformer */
    private $participationTypeTransformer;

    /** @var TemplateObjectFilterTransformer */
    private $templateObjectFilterTransformer;

    /** @var MessageTransformer */
    private $messageTransformer;

    /** @var KeywordTransformer */
    private $keywordTransformer;

    public function __construct(
        NullableTransformer $nullableTransformer,
        RadioTransformer $radioTransformer,
        TaggedNomenclatureTransformer $taggedNomenclatureTransformer,
        TextTransformer $textTransformer,
        ParticipationTypeTransformer $participationTypeTransformer,
        TemplateObjectFilterTransformer $templateObjectFilterTransformer,
        MessageTransformer $messageTransformer,
        KeywordTransformer $keywordTransformer
    ) {
        $this->nullableTransformer = $nullableTransformer;
        $this->radioTransformer = $radioTransformer;
        $this->taggedNomenclatureTransformer = $taggedNomenclatureTransformer;
        $this->textTransformer = $textTransformer;
        $this->participationTypeTransformer = $participationTypeTransformer;
        $this->templateObjectFilterTransformer = $templateObjectFilterTransformer;
        $this->messageTransformer = $messageTransformer;
        $this->keywordTransformer = $keywordTransformer;
    }

    public function transform(Condition $condition): array
    {
        $queries = [];
        $operator = $this->getOperator($condition);

        foreach ($condition->getRules() as $rule) {
            $query = $this->buildQueries($condition, $rule);

            if (!$query) {
                continue;
            }

            $queries['bool'][$operator][] = $query;
        }

        return $queries;
    }

    private function buildQueries(Condition $condition, RuleInterface $rule): array
    {
        if ($rule instanceof Condition) {
            $subQueries = [];
            $operator = $this->getOperator($rule);

            foreach ($rule->getRules() as $subRule) {
                $subQueries['bool'][$operator][] = $this->buildQueries($condition, $subRule);
            }

            return $subQueries;
        }

        /** @var Field $field */
        $field = $rule;

        return $this->buildFieldQuery($condition, $field);
    }

    private function buildFieldQuery(Condition $condition, Field $field): array
    {
        if ($this->taggedNomenclatureTransformer->supports($field)) {
            $this->taggedNomenclatureTransformer->setEventAndLocale(
                $condition->getEvent(),
                $condition->getLocale()
            );

            return $this->taggedNomenclatureTransformer->transform($field);
        }

        if ($this->participationTypeTransformer->supports($field)) {
            $this->participationTypeTransformer->setEventAndLocale(
                $condition->getEvent(),
                $condition->getLocale()
            );

            return $this->participationTypeTransformer->transform($field);
        }

        if ($this->messageTransformer->supports($field)) {
            $this->messageTransformer->setEventAndLocale(
                $condition->getEvent(),
                $condition->getLocale()
            );

            return $this->messageTransformer->transform($field);
        }

        if ($this->templateObjectFilterTransformer->supports($field)) {
            return $this->templateObjectFilterTransformer->transform($field);
        }

        if ($this->keywordTransformer->supports($field)) {
            return $this->keywordTransformer->transform($field);
        }

        if ($this->nullableTransformer->supports($field)) {
            return $this->nullableTransformer->transform($field);
        }

        if ($this->textTransformer->supports($field)) {
            return $this->textTransformer->transform($field);
        }

        if ($this->radioTransformer->supports($field)) {
            return $this->radioTransformer->transform($field);
        }

        return [];
    }

    private function getOperator(Condition $condition): string
    {
        return $condition->getLogicalOperator() instanceof LogicalOperatorAnd ? 'must' : 'should';
    }
}

<?php

namespace Proximum\Vimeet\Domain\Rule;

use Proximum\Vimeet\Domain\Rule\Exception\NoRuleException;

class Composer
{
    /**
     * @var RuleSorter
     */
    private $ruleSorter;

    /**
     * @param RuleSorter $ruleSorter
     */
    public function __construct(RuleSorter $ruleSorter)
    {
        $this->ruleSorter = $ruleSorter;
    }

    /**
     * @param array $rules
     *
     * @throws NoRuleException
     *
     * @return ComposedRule
     */
    public function compose(array $rules)
    {
        $composedRule = new ComposedRule();

        $this->ruleSorter->sort($rules);

        if (empty($rules)) {
            throw new NoRuleException('No rule found.');
        }

        $rule = reset($rules);
        $composedRule->rule = $rule;
        $composedRule->tags = $rule->getWhat();

        return $composedRule;
    }
}

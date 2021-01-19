<?php

namespace Proximum\Vimeet\Domain\Rule;

use Proximum\Vimeet\Domain\Model\Rule;

class RuleSorter
{
    /**
     * Define rule priority.
     *
     * @param Rule $rule
     *
     * @return int
     */
    private function priority(Rule $rule)
    {
        if ($rule->getSeerType() && $rule->getSeeableType()) {
            return 1;
        }

        if ($rule->getSeerCategory() && $rule->getSeeableType()) {
            return 2;
        }

        if ($rule->getSeerType() && $rule->getSeeableCategory()) {
            return 3;
        }

        if ($rule->getSeerCategory() && $rule->getSeeableCategory()) {
            return 4;
        }

        return 5;
    }

    /**
     * Sort rules by priority.
     *
     * @param array $rules
     */
    public function sort(array &$rules)
    {
        usort($rules, function (Rule $one, Rule $another) {
            $onePriority = $this->priority($one);
            $anotherPriority = $this->priority($another);

            return $onePriority < $anotherPriority ? -1 : ($onePriority > $anotherPriority ? 1 : 0);
        });
    }
}

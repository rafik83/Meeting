<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Rule;

use Proximum\Vimeet\Domain\Model\Rule;

class RuleSorter
{
    /**
     * Define rule priority
     *
     * @param Rule $rule
     *
     * @return int
     */
    private function priority(Rule $rule)
    {
        return $rule->getSeeableType() ? ($rule->getSeerType() ? 1 : 2) : ($rule->getSeeableType() ? 3 : 4);
    }

    /**
     * Sort rules by priority
     *
     * @param array $rules
     */
    public function sort(array &$rules)
    {
        usort($rules, function (Rule $one, Rule $another) {
            $onePriority     = $this->priority($one);
            $anotherPriority = $this->priority($another);

            return $onePriority < $anotherPriority ? 1  : $onePriority > $anotherPriority ? -1 : 0;
        });
    }
}

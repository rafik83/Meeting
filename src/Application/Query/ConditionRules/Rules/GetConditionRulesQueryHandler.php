<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\ConditionRules\Rules;

use Proximum\Vimeet\Domain\ConditionRules\ConditionRulesParser;
use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;

class GetConditionRulesQueryHandler
{
    public function handle(GetConditionRulesQuery $query): RuleInterface
    {
        return ConditionRulesParser::parse($query->event, $query->locale, $query->rules);
    }
}

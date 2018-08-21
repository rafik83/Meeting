<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\ConditionRules\Rules;

use Proximum\Vimeet\Application\Query\Query;

class GetConditionRulesQuery implements Query
{
    /** @var array */
    public $rules;

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }
}

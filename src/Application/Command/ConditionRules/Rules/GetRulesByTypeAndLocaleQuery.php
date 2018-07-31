<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\ConditionRules\Rules;

use Proximum\Vimeet\Application\Query\Query;

class GetRulesByTypeAndLocaleQuery implements Query
{
    /** @var string */
    public $type;

    /** @var string */
    public $locale;

    public function __construct(string $type, string $locale)
    {
        $this->type = $type;
        $this->locale = $locale;
    }
}

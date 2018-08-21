<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer;

use Proximum\Vimeet\Domain\ConditionRules\View\Condition;

interface ConditionRulesTransformerInterface
{
    public function transform(Condition $condition): array;
}

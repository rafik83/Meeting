<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Domain\ConditionRules\View\Field;

interface InputTransformerInterface
{
    public static function transform(Field $field): array;

    public static function supports(Field $field): bool;
}

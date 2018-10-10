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

class RadioTransformer implements InputTransformerInterface
{
    public static function transform(Field $field): array
    {
        if (!self::supports($field)) {
            return [];
        }

        return [
            'term' => [
                $field->getField() => $field->getValue(),
            ],
        ];
    }

    public static function supports(Field $field): bool
    {
        return 'radio' === $field->getInput();
    }
}

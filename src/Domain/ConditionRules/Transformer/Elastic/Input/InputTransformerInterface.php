<?php

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Domain\ConditionRules\View\Field;

interface InputTransformerInterface
{
    public function transform(Field $field): array;

    public function supports(Field $field): bool;
}

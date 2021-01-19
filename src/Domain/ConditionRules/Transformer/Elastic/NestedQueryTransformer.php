<?php

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic;

use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class NestedQueryTransformer
{
    public static function transformIfNeeded(Field $field, array $query): array
    {
        if (false === strpos($field->getField(), '.')) {
            return $query;
        }

        $fields = explode('.', $field->getField());

        return [
            'nested' => [
                'path' => $fields[0],
                'query' => $query,
            ]
        ];
    }
}

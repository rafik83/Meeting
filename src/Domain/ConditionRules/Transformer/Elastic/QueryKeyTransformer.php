<?php

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class QueryKeyTransformer
{
    public static function getQueryKey(Field $field): string
    {
        $mapping = TypesMapping::SEARCH_MAPPING[$field->getField()];
        $comparisonClassName = \get_class($field->getComparisonOperator());

        if (isset($mapping['rules']) && \array_key_exists($comparisonClassName, $mapping['rules'])) {
            return TypesMapping::SEARCH_MAPPING[$field->getField()]['rules'][$comparisonClassName]['path'] ?? '';
        }

        return TypesMapping::SEARCH_MAPPING[$field->getField()]['path'] ?? '';
    }
}

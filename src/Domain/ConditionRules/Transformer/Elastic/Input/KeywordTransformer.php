<?php


namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class KeywordTransformer implements InputTransformerInterface
{
    public function transform(Field $field): array
    {
        if (!$this->supports($field)) {
            return [];
        }

        return $this->getQuery($field);
    }

    private function getQuery(Field $field): array
    {
        $query = [
            'query_string' => [
                'default_field' => (TypesMapping::SEARCH_MAPPING[$field->getField()]['path'] ?? '').'.label',
                'query' => $this->getFilterQuery($field),
                'default_operator' => 'AND',
            ],
        ];

        return [
            'nested' => [
                'path' => TypesMapping::SEARCH_MAPPING[$field->getField()]['path'] ?? '',
                'query' => $query,
            ],
        ];
    }

    private function getFilterQuery(Field $field): string
    {
        $cleanValue = preg_replace(TextTransformer::CLEAN_SEARCH_FIELD_REGEX, ' ', $field->getValue());

        return '*'.$cleanValue.'*';
    }

    public function supports(Field $field): bool
    {
        return 'keywords' === $field->getField();
    }
}

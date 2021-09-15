<?php

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class TemplateObjectFilterTransformer implements InputTransformerInterface
{
    public function transform(Field $field): array
    {
        if (!$this->supports($field)) {
            return [];
        }

        $templateKey = explode('.', $field->getField())[1];

        if ('radio' === $field->getInput()) {
            $query = $this->radioQuery($field, $templateKey);
        } else {
            $query = $this->checkboxQuery($field, $templateKey);
        }

        return [
            'nested' => [
                'path' => TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS,
                'query' => $query,
            ],
        ];
    }

    private function radioQuery(Field $field, string $templateKey): array
    {
        // In case the value is not filled
        if (false === $field->getValue()) {
            $query = [
                'must' => [
                    [
                        'match' => [
                            TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS.'.key' => $templateKey,
                        ],
                    ],
                    [
                        'match' => [
                            TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS.'.value' => 'none',
                        ],
                    ],
                ],
            ];
        } else {
            // In case the value is filled
            $query = [
                'must' => [
                    'match' => [
                        TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS.'.key' => $templateKey,
                    ],
                ],
                'must_not' => [
                    'match' => [
                        TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS.'.value' => 'none',
                    ],
                ],
            ];
        }

        return [
            'bool' => $query,
        ];
    }

    private function checkboxQuery(Field $field, string $templateKey): array
    {
        if (empty($field->getValue())) {
            return [];
        }

        $matches = [];

        foreach ($field->getValue() as $value) {
            $matches[] = [
                'match' => [
                    TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS.'.value' => $value,
                ],
            ];
        }

        return [
            [
                'bool' => [
                    'must' => [
                        'match' => [
                            TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS.'.key' => $templateKey,
                        ],
                    ]
                ],
            ],
            [
                'bool' => [
                    'should' => $matches,
                ]
            ]
        ];
    }

    public function supports(Field $field): bool
    {
        return false !== stripos($field->getField(), TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS);
    }
}

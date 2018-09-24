<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\ConditionRules\Filters;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\ConditionRules\ComparisonOperatorsByType;

class GetFiltersByTypeAndLocaleQueryHandler
{
    private const TRANSLATION_KEY = 'form.filter.%s.label';
    private const FIELDS = [
        'user' => [
            TypesMapping::USER_EVENT_VIEW_FIRSTNAME => ['type' => 'string'],
            TypesMapping::USER_EVENT_VIEW_LASTNAME => ['type' => 'string'],
            TypesMapping::USER_EVENT_VIEW_EMAIL => ['type' => 'string'],
            TypesMapping::USER_EVENT_VIEW_IS_VISIO => ['type' => 'boolean'],
            TypesMapping::USER_EVENT_VIEW_IS_VISIO_TESTED => ['type' => 'boolean'],
        ],
        'sheet' => [
            'sheetName' => ['type' => 'string'],
            'participants.lastname' => ['type' => 'string'],
            'participants.email'=> ['type' => 'string'],
        ],
    ];

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function handle(GetFiltersByTypeAndLocaleQuery $query): array
    {
        if (!array_key_exists($query->type, self::FIELDS)) {
            throw new \InvalidArgumentException(sprintf('Query type "%s" is not available.', $query->type));
        }

        return $this->getFilters(self::FIELDS[$query->type], $query->locale);
    }

    private function getFilters(array $fields, string $locale): array
    {
        $filters = [];

        foreach ($fields as $id => $field) {
            $type = $field['type'];
            $filter = [
                'id' => $id,
                'label' => $this->translate($id, $locale),
                'type' => $type,
                'operators' => ComparisonOperatorsByType::OPERATORS[$type] ?? [],
            ];

            if ('boolean' === $type) {
                $filter = array_merge($filter, $this->getBooleanExtraParameters($locale));
            }

            $filters[] = $filter;
        }

        return $filters;
    }

    private function getBooleanExtraParameters(string $locale): array
    {
        return [
            'type' => 'integer',
            'input' => 'radio',
            'values' => [
                0 => $this->translate('boolean.no', $locale),
                1 => $this->translate('boolean.yes', $locale),
            ],
        ];
    }

    private function translate(string $key, string $locale): string
    {
        return $this->translator->trans(sprintf(self::TRANSLATION_KEY, $key), [], 'forms', $locale);
    }
}

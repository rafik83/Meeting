<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\ConditionRules\Filters;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\ConditionRules\ComparisonOperatorsByType;

class GetFiltersByTypeAndLocaleQueryHandler
{
    private const TRANSLATION_KEY = 'form.filter.%s.label';
    private const USER_FIELDS = [
        'firstName' => ['type' => 'string'],
        'lastName' => ['type' => 'string'],
        'email' => ['type' => 'string'],
    ];

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function handle(GetFiltersByTypeAndLocaleQuery $query): array
    {
        if ('user' === $query->type) {
            return $this->getFilters(self::USER_FIELDS, $query->locale);
        }

        throw new \InvalidArgumentException(sprintf('Query type "%s" is not available.', $query->type));
    }

    private function getFilters(array $fields, string $locale): array
    {
        $filters = [];

        foreach ($fields as $id => $field) {
            $type = $field['type'];
            $filters[] = [
                'id' => $id,
                'label' => $this->translator->trans(sprintf(self::TRANSLATION_KEY, $id), [], 'forms', $locale),
                'type' => $type,
                'operators' => ComparisonOperatorsByType::OPERATORS[$type] ?? [],
            ];
        }

        return $filters;
    }
}

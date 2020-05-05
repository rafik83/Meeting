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
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Event\Filter\GetTemplateFiltersQuery;
use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;
use Proximum\Vimeet\Domain\Catalog\View\NomenclatureFilterView;
use Proximum\Vimeet\Domain\ConditionRules\ComparisonOperatorsByType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\BooleanTemplateFilter;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Repository\Messaging\MessageRepository;

class GetFiltersByTypeAndLocaleQueryHandler
{
    private const TRANSLATION_KEY = 'form.filter.%s.label';
    private const FIELDS = [
        'user' => [
            TypesMapping::USER_EVENT_VIEW_FIRSTNAME => [
                'type' => 'string',
                'optgroup' => 'optgroup.participantInfo',
            ],
            TypesMapping::USER_EVENT_VIEW_LASTNAME => [
                'type' => 'string',
                'optgroup' => 'optgroup.participantInfo',
            ],
            TypesMapping::USER_EVENT_VIEW_EMAIL => [
                'type' => 'string',
                'optgroup' => 'optgroup.participantInfo',
            ],
            TypesMapping::USER_EVENT_VIEW_IS_VISIO => [
                'type' => 'boolean',
                'optgroup' => 'optgroup.participantManagement',
            ],
            TypesMapping::USER_EVENT_VIEW_IS_VISIO_TESTED => [
                'type' => 'boolean',
                'optgroup' => 'optgroup.participantManagement',
            ],
        ],
        'sheet' => [
            TypesMapping::SHEET_VIEW_SHEET_NAME => [
                'type' => 'string',
                'optgroup' => 'optgroup.sheetInfo'
            ],
            TypesMapping::SHEET_VIEW_SPOT_REFERENCE => [
                'type' => 'string',
                'optgroup' => 'optgroup.sheetInfo'
            ],
            TypesMapping::SHEET_VIEW_PARTICIPANTS_LASTNAME => [
                'type' => 'string',
                'optgroup' => 'optgroup.participantInfo'
            ],
            TypesMapping::SHEET_VIEW_PARTICIPANTS_EMAIL => [
                'type' => 'string',
                'optgroup' => 'optgroup.participantInfo'
            ],
        ],
    ];

    /** @var TranslatorInterface */
    private $translator;

    /** @var TaggedNomenclatureFilterGetter */
    private $taggedNomenclatureFilterGetter;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var MessageRepository */
    private $messageRepository;

    public function __construct(
        TranslatorInterface $translator,
        TaggedNomenclatureFilterGetter $taggedNomenclatureFilterGetter,
        TypeRepositoryInterface $typeRepository,
        QueryBusInterface $queryBus,
        MessageRepository $messageRepository
    ) {
        $this->translator = $translator;
        $this->taggedNomenclatureFilterGetter = $taggedNomenclatureFilterGetter;
        $this->typeRepository = $typeRepository;
        $this->queryBus = $queryBus;
        $this->messageRepository = $messageRepository;
    }

    public function handle(GetFiltersByTypeAndLocaleQuery $query): array
    {
        if (!array_key_exists($query->type, self::FIELDS)) {
            throw new \InvalidArgumentException(sprintf('Query type "%s" is not available.', $query->type));
        }

        $filters = $this->getFilters(self::FIELDS[$query->type], $query->locale);

        if ('sheet' === $query->type) {
            $filters = array_merge(
                $filters,
                $this->getNomenclatureFilters($query->event, $query->locale),
                $this->getMessageFilters($query->event, $query->locale),
                $this->getKeywordFilters($query->locale)
            );
        }

        if ('user' === $query->type) {
            $filters = array_merge(
                $filters,
                $this->getParticipationTypeFilters($query->event, $query->locale),
                $this->getTemplateObjectFilters($query->event, $query->locale)
            );
        }

        return $filters;
    }

    private function getNomenclatureFilters(Event $event, string $locale): array
    {
        $filters = [];
        $nomenclatureFilterViews = $this->taggedNomenclatureFilterGetter->getNomenclaturesItemsByEvent($event, $locale);

        /** @var NomenclatureFilterView $nomenclatureFilterView */
        foreach ($nomenclatureFilterViews as $nomenclatureFilterView) {
            $filter = [
                'id' => sprintf('%s.%s', TypesMapping::SHEET_VIEW_TAGGED_NOMENCLATURE, $nomenclatureFilterView->id),
                'label' => $nomenclatureFilterView->title,
                'type' => 'string',
                'input' => 'select',
                'plugin' => 'select2',
                'multiple' => true,
                'optgroup' => $this->translate('optgroup.nomenclature', $locale),
                'values' => $nomenclatureFilterView->items,
                'operators' => ComparisonOperatorsByType::OPERATORS['nomenclature'] ?? [],
            ];

            $filters[] = $filter;
        }

        return $filters;
    }

    private function getKeywordFilters(string $locale): array
    {
        $filters = [];
        $id = TypesMapping::SHEET_VIEW_KEYWORD;

        $filters[] = [
            'id' => $id,
            'label' => $this->translate($id, $locale),
            'type' => 'string',
            'optgroup' => $this->translate('optgroup.sheetInfo', $locale),
            'operators' => ComparisonOperatorsByType::OPERATORS['keywords'] ?? [],
        ];

        return $filters;
    }

    private function getMessageFilters(Event $event, string $locale): array
    {
        $filters = [];
        $items = [];
        $messages = $this->messageRepository->findByEventOrderByName($event);

        /** @var Message $message */
        foreach ($messages as $message) {
            $items[$message->getId()] = $message->getName();
        }

        $filter = [
            'id'        => TypesMapping::SHEET_MESSAGES_RECEIVED,
            'label'     => $this->translate('messaging.received', $locale),
            'type'      => 'string',
            'input'     => 'select',
            'plugin'    => 'select2',
            'multiple'  => true,
            'optgroup'  => $this->translate('optgroup.sheetInfo', $locale),
            'values'    => $items,
            'operators' => ComparisonOperatorsByType::OPERATORS['message'] ?? [],
        ];

        $filters[] = $filter;

        return $filters;
    }

    private function getTemplateObjectFilters(Event $event, string $locale): array
    {
        $filters = [];
        $templateFilters = $this->queryBus->handle(new GetTemplateFiltersQuery($event, Tag::PARTICIPANT_DATA));

        foreach ($templateFilters as $objectKey => $templateFilter) {
            $filter = [
                'id' => sprintf('templateObjectFilters.%s', $objectKey),
                'label' => $templateFilter->getLabel(),
                'optgroup' => $this->translate('optgroup.tag_participant', $locale),
            ];

            if ($templateFilter instanceof BooleanTemplateFilter) {
                $filter = array_merge($filter, [
                    'type' => 'string',
                    'input' => 'checkbox',
                    'values' => [
                        'true' => $this->translate('boolean.yes', $locale),
                        'false' => $this->translate('boolean.no', $locale),
                        'none' => $this->translate('not_filled', $locale)
                    ],
                    'operators' => ['in'],
                ]);
            } else {
                $filter = array_merge($filter, [
                    'type' => 'boolean',
                    'input' => 'radio',
                    'values' => [
                        'true' => $this->translate('filled', $locale),
                        'false' => $this->translate('not_filled', $locale),
                    ],
                    'operators' => ComparisonOperatorsByType::OPERATORS['boolean'],
                ]);
            }

            $filters[] = $filter;
        }

        return $filters;
    }

    private function getParticipationTypeFilters(Event $event, string $locale): array
    {
        $types = $this->typeRepository->getTypesTitleByEventAndLocale(
            $event,
            $event->getAvailableLocale($locale)
        );

        return [
            [
                'id' => 'participation_type',
                'label' => $this->translate('participation_type', $locale),
                'type' => 'string',
                'input' => 'select',
                'plugin' => 'select2',
                'multiple' => true,
                'optgroup' => $this->translate('optgroup.sheetInfo', $locale),
                'values' => $types,
                'operators' => ComparisonOperatorsByType::OPERATORS['participation_type'] ?? [],
            ]
        ];
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
                'optgroup' => $this->translate($field['optgroup'], $locale),
                'operators' => ComparisonOperatorsByType::OPERATORS[$type] ?? [],
            ];

            if ('boolean' === $type) {
                $filter = array_merge($filter, $this->getExtraParametersForBooleanField($locale));
            }

            $filters[] = $filter;
        }

        return $filters;
    }

    private function getExtraParametersForBooleanField(string $locale): array
    {
        return [
            'type' => 'boolean',
            'input' => 'radio',
            'values' => [
                'false' => $this->translate('boolean.no', $locale),
                'true' => $this->translate('boolean.yes', $locale),
            ],
        ];
    }

    private function translate(string $key, string $locale): string
    {
        return $this->translator->trans(sprintf(self::TRANSLATION_KEY, $key), [], 'forms', $locale);
    }
}

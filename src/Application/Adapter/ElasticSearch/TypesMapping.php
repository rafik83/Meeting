<?php

namespace Proximum\Vimeet\Application\Adapter\ElasticSearch;

use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorContains;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotContains;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;

final class TypesMapping
{
    public const USER_EVENT_VIEW_ID = 'uid';
    public const USER_EVENT_VIEW_EVENT_ID = 'eventId';
    public const USER_EVENT_VIEW_USER_ID = 'userId';
    public const USER_EVENT_VIEW_FIRSTNAME = 'firstName';
    public const USER_EVENT_VIEW_LASTNAME = 'lastName';
    public const USER_EVENT_VIEW_EMAIL = 'email';
    public const USER_EVENT_VIEW_LOCALE = 'locale';
    public const USER_EVENT_VIEW_SHEETS = 'sheets';
    public const USER_EVENT_VIEW_SHEETS_ID = 'id';
    public const USER_EVENT_VIEW_IS_VISIO = 'isVisio';
    public const USER_EVENT_VIEW_IS_VISIO_TESTED = 'isVisioTested';
    public const USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS = 'templateObjectFilters';
    public const USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_KEY = 'key';
    public const USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_TYPE = 'type';
    public const USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_VALUE = 'value';
    public const SHEET_VIEW_SHEET_NAME = 'sheetName';
    public const SHEET_VIEW_SPOT_REFERENCE = 'spotReference';
    public const SHEET_VIEW_KEYWORD = 'keywords';
    public const SHEET_VIEW_PARTICIPANTS_LASTNAME = 'participants.lastname';
    public const SHEET_VIEW_PARTICIPANTS_EMAIL = 'participants.email';
    public const SHEET_VIEW_TAGGED_NOMENCLATURE = 'nestedTaggedData';
    public const SHEET_MESSAGES_RECEIVED = 'messagesReceived';

    public const AVAILABLE_TYPES = [
        UserEventView::class => [
            'type' => 'user_event',
            'properties' => [
                self::USER_EVENT_VIEW_ID => [
                    'type' => 'text',
                ],
                self::USER_EVENT_VIEW_EVENT_ID => [
                    'type' => 'integer',
                ],
                self::USER_EVENT_VIEW_USER_ID => [
                    'type' => 'integer',
                ],
                self::USER_EVENT_VIEW_FIRSTNAME => [
                    'type' => 'text',
                    'fielddata' => true,
                ],
                self::USER_EVENT_VIEW_LASTNAME => [
                    'type' => 'text',
                    'fielddata' => true,
                ],
                self::USER_EVENT_VIEW_EMAIL => [
                    'type' => 'text',
                    'analyzer' => 'emailAnalyzer',
                ],
                self::USER_EVENT_VIEW_LOCALE => [
                    'type' => 'text',
                ],
                self::USER_EVENT_VIEW_IS_VISIO => [
                    'type' => 'boolean',
                ],
                self::USER_EVENT_VIEW_IS_VISIO_TESTED => [
                    'type' => 'boolean',
                ],
                self::USER_EVENT_VIEW_SHEETS => [
                    'type' => 'nested',
                    'properties' => [
                        self::USER_EVENT_VIEW_SHEETS_ID => ['type' => 'integer'],
                    ],
                ],
                self::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS => [
                    'type' => 'nested',
                    'properties' => [
                        self::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_KEY => [
                            'type' => 'text',
                        ],
                        self::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_VALUE => [
                            'type' => 'text',
                        ],
                        self::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_TYPE => [
                            'type' => 'text'
                        ],
                    ],
                ],
            ],
        ],
    ];

    public const SEARCH_MAPPING = [
        self::SHEET_VIEW_SHEET_NAME => [
            'path' => 'sheetName.raw',
            'rules' => [
                ComparisonOperatorContains::class => [
                    'path' => self::SHEET_VIEW_SHEET_NAME,
                ],
                ComparisonOperatorNotContains::class => [
                    'path' => self::SHEET_VIEW_SHEET_NAME
                ],
            ],
        ],
        self::SHEET_VIEW_SPOT_REFERENCE => [
            'path' => self::SHEET_VIEW_SPOT_REFERENCE,
        ],
        self::SHEET_VIEW_KEYWORD => [
            'path' => self::SHEET_VIEW_KEYWORD,
        ],
        self::SHEET_VIEW_PARTICIPANTS_LASTNAME => [
            'path' => self::SHEET_VIEW_PARTICIPANTS_LASTNAME,
        ],
        self::SHEET_VIEW_PARTICIPANTS_EMAIL => [
            'path' => self::SHEET_VIEW_PARTICIPANTS_EMAIL,
        ],
        self::SHEET_MESSAGES_RECEIVED => [
            'path' => self::SHEET_MESSAGES_RECEIVED,
        ],
        self::SHEET_VIEW_TAGGED_NOMENCLATURE => [
            'path' => self::SHEET_VIEW_TAGGED_NOMENCLATURE,
            'rules' => [
                'key' => [
                    'path' => self::SHEET_VIEW_TAGGED_NOMENCLATURE.'.values.value',
                ],
                'tag' => [
                    'path' => self::SHEET_VIEW_TAGGED_NOMENCLATURE.'.tag'
                ],
            ],
        ],
        self::USER_EVENT_VIEW_FIRSTNAME => [
            'path' => self::USER_EVENT_VIEW_FIRSTNAME,
        ],
        self::USER_EVENT_VIEW_LASTNAME => [
            'path' => self::USER_EVENT_VIEW_LASTNAME,
        ],
        self::USER_EVENT_VIEW_EMAIL => [
            'path' => self::USER_EVENT_VIEW_EMAIL,
        ],
        self::USER_EVENT_VIEW_IS_VISIO => [
            'path' => self::USER_EVENT_VIEW_IS_VISIO,
        ],
        self::USER_EVENT_VIEW_IS_VISIO_TESTED => [
            'path' => self::USER_EVENT_VIEW_IS_VISIO_TESTED,
        ],
    ];

    public static function getTypeByClass(string $class): string
    {
        return self::AVAILABLE_TYPES[$class]['type'];
    }
}

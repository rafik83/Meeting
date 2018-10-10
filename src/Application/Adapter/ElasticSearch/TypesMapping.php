<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter\ElasticSearch;

use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotEqual;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;

final class TypesMapping
{
    public const USER_EVENT_VIEW_ID = 'id';
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
    public const SHEET_VIEW_SHEET_NAME = 'sheetName';
    public const SHEET_VIEW_SPOT_REFERENCE = 'spotReference';
    public const SHEET_VIEW_PARTICIPANTS_LASTNAME = 'participants.lastname';
    public const SHEET_VIEW_PARTICIPANTS_EMAIL = 'participants.email';

    public const AVAILABLE_TYPES = [
        UserEventView::class => [
            'type' => 'user_event',
            'properties' => [
                self::USER_EVENT_VIEW_ID => [
                    'type' => 'string',
                ],
                self::USER_EVENT_VIEW_EVENT_ID => [
                    'type' => 'integer',
                ],
                self::USER_EVENT_VIEW_USER_ID => [
                    'type' => 'integer',
                ],
                self::USER_EVENT_VIEW_FIRSTNAME => [
                    'type' => 'string',
                ],
                self::USER_EVENT_VIEW_LASTNAME => [
                    'type' => 'string',
                ],
                self::USER_EVENT_VIEW_EMAIL => [
                    'type' => 'string',
                    'analyzer' => 'emailAnalyzer',
                ],
                self::USER_EVENT_VIEW_LOCALE => [
                    'type' => 'string',
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
            ],
        ],
    ];

    public const SEARCH_MAPPING = [
        self::SHEET_VIEW_SHEET_NAME => [
            'path' => self::SHEET_VIEW_SHEET_NAME,
            'rules' => [
                ComparisonOperatorEqual::class => [
                    'path' => 'sheetName.raw',
                ],
                ComparisonOperatorNotEqual::class => [
                    'path' => 'sheetName.raw'
                ],
            ],
        ],
        self::SHEET_VIEW_SPOT_REFERENCE => [
            'path' => self::SHEET_VIEW_SPOT_REFERENCE,
        ],
        self::SHEET_VIEW_PARTICIPANTS_LASTNAME => [
            'path' => self::SHEET_VIEW_PARTICIPANTS_LASTNAME,
        ],
        self::SHEET_VIEW_PARTICIPANTS_EMAIL => [
            'path' => self::SHEET_VIEW_PARTICIPANTS_EMAIL,
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

<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter\ElasticSearch;

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

    public static function getTypeByClass(string $class): string
    {
        return self::AVAILABLE_TYPES[$class]['type'];
    }
}

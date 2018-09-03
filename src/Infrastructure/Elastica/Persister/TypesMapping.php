<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Elastica\Persister;

use Proximum\Vimeet\Domain\UserEventView\UserEventView;

final class TypesMapping
{
    public const AVAILABLE_TYPES = [
        UserEventView::class => [
            'type' => 'user_event',
            'properties' => [
                'id' => [
                    'type' => 'string',
                ],
                'eventId' => [
                    'type' => 'integer',
                ],
                'userId' => [
                    'type' => 'integer',
                ],
                'firstName' => [
                    'type' => 'string',
                ],
                'lastName' => [
                    'type' => 'string',
                ],
                'email' => [
                    'type' => 'string',
                    'analyzer' => 'emailAnalyzer',
                ],
                'locale' => [
                    'type' => 'string',
                ],
                'sheets' => [
                    'type' => 'nested',
                    'properties' => [
                        'id' => ['type' => 'integer'],
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

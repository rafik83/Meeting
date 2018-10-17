<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\StaticFormulation;

class Constant
{
    public const STATIC_FORMULATION_KEY_SHEET = 'sheet';
    public const STATIC_FORMULATION_KEY_CATALOG = 'catalog';
    public const STATIC_FORMULATION_KEY_MEETING_REQUEST = 'meeting_request';
    public const STATIC_FORMULATION_KEY_PACKAGE = 'package';
    public const STATIC_FORMULATION_KEY_AGENDA = 'agenda';
    public const STATIC_FORMULATION_KEY_PROGRAM = 'program';
    public const STATIC_FORMULATION_KEY_BILLING = 'billing';
    public const STATIC_FORMULATION_KEY_BADGE = 'badge';
    public const STATIC_FORMULATION_KEY_MEMBER_SPACE = 'member_space';

    public const STATIC_FORMULATION_LIST = [
        self::STATIC_FORMULATION_KEY_SHEET => [
            'label' => 'navigation.category.sheet',
        ],
        self::STATIC_FORMULATION_KEY_CATALOG => [
            'label' => 'navigation.category.catalog',
        ],
        self::STATIC_FORMULATION_KEY_MEETING_REQUEST => [
            'label' => 'navigation.category.meeting',
        ],
        self::STATIC_FORMULATION_KEY_PACKAGE => [
            'label' => 'navigation.category.package',
        ],
        self::STATIC_FORMULATION_KEY_AGENDA => [
            'label' => '',
        ],
        self::STATIC_FORMULATION_KEY_PROGRAM => [
            'label' => 'navigation.category.program',
        ],
        self::STATIC_FORMULATION_KEY_BILLING => [
            'label' => 'navigation.category.billing',
        ],
        self::STATIC_FORMULATION_KEY_MEMBER_SPACE => [
            'label' => 'navigation.category.member_space',
        ],
        self::STATIC_FORMULATION_KEY_BADGE => [
            'label' => 'navigation.category.badge',
        ],
    ];
}

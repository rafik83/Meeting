<?php

namespace Proximum\Vimeet\Domain\StaticFormulation;

use Proximum\Vimeet\Application\Components\Navigation\Category;

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
    public const STATIC_FORMULATION_KEY_FORMS = 'forms';
    public const STATIC_FORMULATION_KEY_VISIO_TEST = 'visio_test';

    public const STATIC_FORMULATION_LIST = [
        self::STATIC_FORMULATION_KEY_SHEET => [
            'label' => 'navigation.category.sheet',
            'categoryKey' => Category::SHEET,
        ],
        self::STATIC_FORMULATION_KEY_CATALOG => [
            'label' => 'navigation.category.catalog',
            'categoryKey' => Category::CATALOG,
        ],
        self::STATIC_FORMULATION_KEY_MEETING_REQUEST => [
            'label' => 'navigation.category.meeting',
            'categoryKey' => Category::MEETING,
        ],
        self::STATIC_FORMULATION_KEY_PACKAGE => [
            'label' => 'navigation.category.package',
            'categoryKey' => Category::PACKAGE,
        ],
        self::STATIC_FORMULATION_KEY_AGENDA => [
            'label' => 'navigation.category.planning',
            'categoryKey' => Category::AGENDA,
        ],
        self::STATIC_FORMULATION_KEY_PROGRAM => [
            'label' => 'navigation.category.program',
            'categoryKey' => Category::PROGRAM,
        ],
        self::STATIC_FORMULATION_KEY_BILLING => [
            'label' => 'navigation.category.billing',
            'categoryKey' => Category::BILLING,
        ],
        self::STATIC_FORMULATION_KEY_MEMBER_SPACE => [
            'label' => 'navigation.category.member_space',
            'categoryKey' => Category::MEMBER_SPACE,
        ],
        self::STATIC_FORMULATION_KEY_BADGE => [
            'label' => 'navigation.category.badge',
            'categoryKey' => Category::BADGE,
        ],
        self::STATIC_FORMULATION_KEY_FORMS => [
            'label' => 'navigation.category.forms',
            'categoryKey' => Category::FORMS,
        ],
        self::STATIC_FORMULATION_KEY_VISIO_TEST => [
            'label' => 'navigation.category.visio_test',
            'categoryKey' => Category::VISIO,
        ],
    ];
}

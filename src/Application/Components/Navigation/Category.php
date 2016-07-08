<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Navigation;

final class Category
{
    const MEMBER_SPACE = 'navigation.category.member_space';
    const BILLING      = 'navigation.category.billing';
    const SHEET        = 'navigation.category.sheet';
    const PACKAGE      = 'navigation.category.package';
    const CATALOG      = 'navigation.category.catalog';
    const HAPPENNING   = 'navigation.category.happenning';
    const PLANNING     = 'navigation.category.planning';

    static public $categories = [
        self::MEMBER_SPACE => self::MEMBER_SPACE,
        self::BILLING      => self::BILLING,
        self::SHEET        => self::SHEET,
        self::PACKAGE      => self::PACKAGE,
        self::CATALOG      => self::CATALOG,
        self::HAPPENNING   => self::HAPPENNING,
        self::PLANNING     => self::PLANNING,
    ];
}

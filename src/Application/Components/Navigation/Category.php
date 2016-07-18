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
    const MEMBER_SPACE      = 'navigation.category.member_space';
    const MEMBER_SPACE_ICON = 'icon-Clef';

    const BILLING      = 'navigation.category.billing';
    const BILLING_ICON = 'icon-Note_1';

    const SHEET      = 'navigation.category.sheet';
    const SHEET_ICON = 'icon-Document_1';

    const PACKAGE      = 'navigation.category.package';
    const PACKAGE_ICON = 'icon-Panier_2';

    const CATALOG      = 'navigation.category.catalog';
    const CATALOG_ICON = 'icon-Contact_1';

    const HAPPENING      = 'navigation.category.happening';
    const HAPPENING_ICON = 'icon-RDV';

    const PLANNING      = 'navigation.category.planning';
    const PLANNING_ICON = 'icon-Calendrier';

    static public $categories = [
        self::MEMBER_SPACE,
        self::BILLING,
        self::SHEET,
        self::PACKAGE,
        self::CATALOG,
        self::HAPPENING,
        self::PLANNING,
    ];
}

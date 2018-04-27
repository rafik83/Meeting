<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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

    const MEETING      = 'navigation.category.meeting';
    const MEETING_ICON = 'icon-RDV';

    const PLANNING      = 'navigation.category.planning';
    const PLANNING_ICON = 'icon-Calendrier';

    const AGENDA      = 'navigation.category.planning';
    const AGENDA_ICON = 'icon-Calendrier';

    const PROGRAM       = 'navigation.category.program';
    const PROGRAM_ICON  = 'icon-PresFlash_2';

    public static $categories = [
        self::MEMBER_SPACE,
        self::BILLING,
        self::SHEET,
        self::PACKAGE,
        self::CATALOG,
        self::MEETING,
        self::PLANNING,
        self::PROGRAM,
    ];
}

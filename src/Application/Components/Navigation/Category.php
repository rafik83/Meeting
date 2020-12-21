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

    const CONTACT_LIST      = 'navigation.category.contact';
    const CONTACT_LIST_ICON = 'icon-RDV';

    const PLANNING      = 'navigation.category.planning';
    const PLANNING_ICON = 'icon-Calendrier';

    const AGENDA      = 'navigation.category.planning';
    const AGENDA_ICON = 'icon-Calendrier';

    const PROGRAM       = 'navigation.category.program';
    const PROGRAM_ICON  = 'icon-PresFlash_2';

    const BADGE      = 'navigation.category.badge';
    const BADGE_ICON = 'icon-Badge_1';

    const BADGE_SCAN      = 'navigation.category.badge_scan';
    const BADGE_SCAN_ICON = 'icon-Photo';

    const LENI_BADGE_LINK = 'navigation.category.leni_badge_link';
    const LENI_BADGE_LINK_ICON = 'icon-Badge_1';

    const FORMS      = 'navigation.category.forms';
    const FORMS_ICON = 'icon-Info_1';

    public const VISIO = 'navigation.category.visio_test';
    public const VISIO_ICON = 'icon-Video_2';

    const NETWORKING      = 'navigation.category.networking';
    const NETWORKING_ICON = 'icon-Travail';

    const CUSTOM_BUTTON_ICON = 'icon-DemandeRDV';
    const CUSTOM_BUTTON_ICON_2 = 'icon-Suivant_1';

    public static $categories = [
        self::MEMBER_SPACE,
        self::BILLING,
        self::SHEET,
        self::PACKAGE,
        self::CATALOG,
        self::MEETING,
        self::PLANNING,
        self::PROGRAM,
        self::BADGE,
        self::FORMS,
        self::VISIO,
        self::NETWORKING,
    ];
}

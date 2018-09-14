<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Navigation;

final class Route
{
    public const EVENT = 'event';
    public const DEFAULT_EVENT = 'default_event';

    public const EXTERNAL_CATALOG = 'event_catalog_external_index';

    public const LOGIN = 'event_login';
    public const EVENT_LOGIN_CHECK = 'event_login_check';
    public const USER_EVENT_AUTHENTICATION_TOKEN_LOGIN = 'vimeet_event_authentication_token_login';

    public const SHEET = [
        'event_sheet_default',
        'event_sheet_locale',
        'event_sheet_update',
    ];

    public const CATALOG_INDEX = 'event_catalog_index';
    public const CATALOG_VIEW_OTHER_SHEET = 'event_catalog_complete_sheet';
    public const CATALOG_MEETING_REQUEST = 'event_catalog_sheet_meeting_request';
    public const MEETING_REQUEST_LIST = 'event_meeting_list_request';

    public const PACKAGE = [
        'event_package_step',
        'event_package_add_participant',
        'event_package_remove_participant',
        'event_package_summary',
        'event_package_summary_fill_billing_info',
        'event_package_remove_promotion_code',
        'event_package_payment',
        'event_package_payment_prepare_paypal',
        'event_package_payment_done',
        'event_package_payment_pay_remaining',
        'create_transaction',
        'event_payment_info',
    ];

    public const ORDER = [
        'event_order_list',
        'event_pro_forma',
        'event_order_summary_total',
    ];

    public const AGENDA = [
        'event_agenda',
        'event_agenda_participant',
        'event_unavailability_create',
    ];

    public const PROGRAM = 'happening_program';
    public const BADGE = 'event_sheet_user_badge';
    public const NOTIFICATION = 'event_notification_list';

    public static function isSheet(string $route): bool
    {
        return \in_array($route, self::SHEET, true);
    }

    public static function isPackage(string $route): bool
    {
        return \in_array($route, array_merge(self::PACKAGE, self::ORDER), true);
    }

    public static function isCatalog(string $route): bool
    {
        return \in_array($route,
            [
                self::CATALOG_INDEX,
                self::CATALOG_VIEW_OTHER_SHEET,
                self::CATALOG_MEETING_REQUEST,
            ],
            true
        );
    }

    public static function isMeetingRequest(string $route): bool
    {
        return self::MEETING_REQUEST_LIST === $route;
    }

    public static function isNotification(string $route): bool
    {
        return self::NOTIFICATION === $route;
    }

    public static function isAgenda(string $route): bool
    {
        return \in_array($route, self::AGENDA, true);
    }

    public static function isProgram(string $route): bool
    {
        return self::PROGRAM === $route;
    }

    public static function isBadge(string $route): bool
    {
        return self::BADGE === $route;
    }

    public static function isHeaderDisplayedOnMobile(string $route): bool
    {
        return !\in_array($route,
            [
                self::EXTERNAL_CATALOG,
                self::CATALOG_INDEX,
                self::MEETING_REQUEST_LIST,
                self::BADGE,
            ],
            true
        );
    }
}

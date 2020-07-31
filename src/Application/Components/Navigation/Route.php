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
        self::SHEET_DEFAULT,
        'event_sheet_locale',
        'event_sheet_update',
    ];

    public const SHEET_DEFAULT = 'event_sheet_default';
    public const SHEET_UPDATE = 'event_sheet_update';

    public const PARTICIPANT = [
        self::PARTICIPANT_ACCOUNT,
    ];

    public const PARTICIPANT_ACCOUNT = 'event_account_participant';

    public const CONTACT_LIST = 'event_contact_index';

    public const CATALOG_INDEX = 'event_catalog_index';
    public const CATALOG_VIEW_OTHER_SHEET = 'event_catalog_complete_sheet';
    public const CATALOG_MEETING_REQUEST = 'event_catalog_sheet_meeting_request';
    public const MEETING_REQUEST_LIST = 'event_meeting_list_request';
    public const MEETING_REQUEST_MERGED_LIST = 'event_meeting_request_merged_list';

    public const MEETING_EVALUATION = 'event_meeting_evaluation';

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
        self::ORDER_LIST,
        self::ORDER_PRO_FORMA,
        'event_order_summary_total',
    ];

    public const ORDER_LIST = 'event_order_list';
    public const ORDER_PRO_FORMA = 'event_pro_forma';

    public const AGENDA = [
        self::AGENDA_DEFAULT,
        'event_agenda_participant',
        'event_unavailability_create',
    ];

    public const AGENDA_DEFAULT = 'event_agenda';
    public const AGENDA_PARTICIPANT = 'event_agenda_participant';
    public const AGENDA_CONFIRMATION = 'event_user_event_token_confirm_agenda';

    public const PROGRAM = 'happening_program';
    public const BADGE_SCAN = 'event_sheet_user_badge_scan';
    public const BADGE = 'event_sheet_user_badge';
    public const BADGE_DOWNLOAD = 'event_sheet_user_badge_download';
    public const NOTIFICATION = 'event_notification_list';

    public const VISIO_TEST_CONFIGURATION = 'event_video_conference_create_network_test';
    public const VISIO_TEST_SHEET_CREATE_TEST = 'event_sheet_video_conference_create_network_test';
    public const VISIO_TEST_SHEET_CONFIGURATION = 'event_sheet_video_conference_network_test';

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
        return \in_array($route, [self::MEETING_REQUEST_LIST, self::MEETING_REQUEST_MERGED_LIST], true);
    }

    public static function isNotification(string $route): bool
    {
        return self::NOTIFICATION === $route;
    }

    public static function isVisioTestConfigurationWithSheetContext(string $route): bool
    {
        return $route === self::VISIO_TEST_SHEET_CONFIGURATION;
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

    public static function isBadgeScan(string $route): bool
    {
        return self::BADGE_SCAN === $route;
    }

    public static function isContactList(string $route): bool
    {
        return self::CONTACT_LIST === $route;
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

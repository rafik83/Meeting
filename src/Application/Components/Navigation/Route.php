<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Navigation;

final class Route
{
    const CATALOG = [
        'event_catalog_index',
        'event_catalog_complete_sheet',
        'event_catalog_sheet_meeting_request',
    ];

    const MEETING_REQUEST = [
        'event_meeting_list_request',
    ];

    const PACKAGE = [
        'event_package',
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

    const ORDER = [
        'event_order_list',
        'event_pro_forma',
        'event_order_summary_total',
    ];

    /**
     * @param string $route
     *
     * @return bool
     */
    public static function isPackage($route)
    {
        return in_array($route, self::PACKAGE) || in_array($route, self::ORDER);
    }

    /**
     * @param string $route
     *
     * @return bool
     */
    public static function isCatalog($route)
    {
        return in_array($route, self::CATALOG);
    }

    /**
     * @param string $route
     *
     * @return bool
     */
    public static function isMeetingRequest($route)
    {
        return in_array($route, self::MEETING_REQUEST);
    }
}

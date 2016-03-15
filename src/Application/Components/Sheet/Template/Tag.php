<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template;

final class Tag
{
    const PARTICIPANT_FIRSTNAME = 'participant_firstname';
    const PARTICIPANT_LASTNAME  = 'participant_lastname';
    const PARTICIPANT_PHONE     = 'participant_phone';
    const BILLING_NAME          = 'billing_name';
    const BILLING_ADDRESS       = 'billing_address';
    const BILLING_CITY          = 'billing_city';
    const BILLING_ZIPCODE       = 'billing_zipcode';
    const BILLING_COUNTRY       = 'billing_country';
    const BILLING_PHONE         = 'billing_phone';
    const BILLING_EMAIL         = 'billing_email';
    const BILLING_ORGANIZATION  = 'billing_organization';
    const BILLING_VAT_NUMBER    = 'billing_vat_number';
    const BILLING_EXTRA         = 'billing_extra';
    const SHEET_ORGANIZATION    = 'sheet_organization';
    const SHEET_PACKAGE         = 'sheet_package';

    /**
     * @return array
     */
    public static function getAll()
    {
        return [
            self::PARTICIPANT_FIRSTNAME,
            self::PARTICIPANT_LASTNAME,
            self::BILLING_NAME,
            self::BILLING_ADDRESS,
            self::BILLING_CITY,
            self::BILLING_ZIPCODE,
            self::BILLING_COUNTRY,
            self::BILLING_PHONE,
            self::BILLING_EMAIL,
            self::BILLING_ORGANIZATION,
            self::BILLING_VAT_NUMBER,
            self::BILLING_EXTRA,
            self::SHEET_ORGANIZATION,
        ];
    }
}

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
    const GENERIC_TAGS_NUMBER = 99;

    // Getter
    const PARTICIPANT_FIRSTNAME       = 'participant_firstname';
    const PARTICIPANT_LASTNAME        = 'participant_lastname';
    const PARTICIPANT_AVATAR          = 'participant_avatar';
    const PARTICIPANT_POSITION        = 'participant_position';
    const PARTICIPANT_PHONE           = 'participant_phone';
    const PARTICIPANT_MOBILE          = 'participant_mobile';
    const PARTICIPANT_ADDRESS         = 'participant_address';
    const PARTICIPANT_ZIPCODE         = 'participant_zipcode';
    const PARTICIPANT_CITY            = 'participant_city';
    const PARTICIPANT_COUNTRY         = 'participant_country';
    const PARTICIPANT_WEBSITE         = 'participant_website';
    const PARTICIPANT_GENDER          = 'participant_gender';
    const BILLING_NAME                = 'billing_name';
    const BILLING_ADDRESS             = 'billing_address';
    const BILLING_CITY                = 'billing_city';
    const BILLING_ZIPCODE             = 'billing_zipcode';
    const BILLING_COUNTRY             = 'billing_country';
    const BILLING_PHONE               = 'billing_phone';
    const BILLING_EMAIL               = 'billing_email';
    const BILLING_ORGANIZATION        = 'billing_organization';
    const BILLING_VAT_NUMBER          = 'billing_vat_number';
    const BILLING_EXTRA               = 'billing_extra';
    const SHEET_TITLE                 = 'sheet_title';
    const SHEET_ORGANIZATION          = 'sheet_organization';
    const SHEET_ORGANIZATION_CATEGORY = 'sheet_organization_category';
    const SHEET_ORGANIZATION_TURNOVER = 'sheet_organization_turnover';
    const SHEET_ORGANIZATION_STAFF    = 'sheet_organization_staff';
    const SHEET_ADDRESS               = 'sheet_address';
    const SHEET_ZIPCODE               = 'sheet_zipcode';
    const SHEET_CITY                  = 'sheet_city';
    const SHEET_COUNTRY               = 'sheet_country';
    const SHEET_WEBSITE               = 'sheet_website';
    const SHEET_PHONE                 = 'sheet_phone';

    // Setter
    const PARTICIPANT_DATA = 'participant_data';
    const SHEET_DATA       = 'sheet_data';

    /**
     * @return array
     */
    public static function getAll()
    {
        return array_merge(
            Tag::getParticipantTags(),
            Tag::getBillingTags(),
            Tag::getSheetTags()
        );
    }

    /**
     * @return array
     */
    public static function getSetters()
    {
        return [
            Tag::PARTICIPANT_DATA,
            Tag::SHEET_DATA,
        ];
    }

    /**
     * @return array
     */
    public static function getParticipantIdentityTags()
    {
        return [
            self::PARTICIPANT_FIRSTNAME,
            self::PARTICIPANT_LASTNAME,
        ];
    }

    /**
     * @return array
     */
    public static function getParticipantTags()
    {
        return [
            self::PARTICIPANT_FIRSTNAME,
            self::PARTICIPANT_LASTNAME,
            self::PARTICIPANT_PHONE,
            self::PARTICIPANT_MOBILE,
            self::PARTICIPANT_POSITION,
            self::PARTICIPANT_AVATAR,
            self::PARTICIPANT_ADDRESS,
            self::PARTICIPANT_ZIPCODE,
            self::PARTICIPANT_CITY,
            self::PARTICIPANT_COUNTRY,
            self::PARTICIPANT_WEBSITE,
            self::PARTICIPANT_GENDER,
        ];
    }

    /**
     * @return array
     */
    public static function getBillingTags()
    {
        return [
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
        ];
    }

    /**
     * @return array
     */
    public static function getSheetTags()
    {
        return [
            self::SHEET_ORGANIZATION,
            self::SHEET_TITLE,
            self::SHEET_ORGANIZATION_CATEGORY,
            self::SHEET_ORGANIZATION_TURNOVER,
            self::SHEET_ORGANIZATION_STAFF,
            self::SHEET_ADDRESS,
            self::SHEET_ZIPCODE,
            self::SHEET_CITY,
            self::SHEET_COUNTRY,
            self::SHEET_WEBSITE,
            self::SHEET_PHONE,
        ];
    }

    /**
     * @return array
     */
    public static function getSeeableTags()
    {
        return [
            self::SHEET_ORGANIZATION,
            self::SHEET_TITLE,
            self::SHEET_ORGANIZATION_CATEGORY,
            self::SHEET_ORGANIZATION_TURNOVER,
            self::SHEET_ORGANIZATION_STAFF,
            self::SHEET_ADDRESS,
            self::SHEET_ZIPCODE,
            self::SHEET_CITY,
            self::SHEET_WEBSITE,
            self::SHEET_COUNTRY,
            self::SHEET_PHONE,
            self::PARTICIPANT_POSITION,
        ];
    }

    /**
     * @return array
     */
    public static function getGenericSheetTags()
    {
        $genericTags = [];

        for ($i = 1; $i <= self::GENERIC_TAGS_NUMBER; $i++) {
            $genericTags[] = 'sheet_generic_tag_' . $i;
        }

        return $genericTags;
    }

    /**
     * @return array
     */
    public static function getTemplateChoiceTags()
    {
        return array_merge(self::getSheetTags(), self::getGenericSheetTags());
    }

    /**
     * @return array
     */
    public static function getRegisterTemplateChoiceTags(): array
    {
        return array_merge(self::getParticipantTags(), self::getSheetTags(), self::getGenericSheetTags());
    }
}

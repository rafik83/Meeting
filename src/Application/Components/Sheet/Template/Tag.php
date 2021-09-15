<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Template;

use Proximum\Vimeet\Domain\Template\View\TemplateTagView;

final class Tag
{
    private const GENERIC_TAGS_NUMBER = 99;
    private const PARTICIPANTS_GENERIC_TAGS_NUMBER = 99;

    // Getter
    public const PARTICIPANT_FIRSTNAME       = 'participant_firstname';
    public const PARTICIPANT_LASTNAME        = 'participant_lastname';
    public const PARTICIPANT_AVATAR          = 'participant_avatar';
    public const PARTICIPANT_POSITION        = 'participant_position';
    public const PARTICIPANT_PHONE           = 'participant_phone';
    public const PARTICIPANT_MOBILE          = 'participant_mobile';
    public const PARTICIPANT_ADDRESS         = 'participant_address';
    public const PARTICIPANT_ZIPCODE         = 'participant_zipcode';
    public const PARTICIPANT_CITY            = 'participant_city';
    public const PARTICIPANT_COUNTRY         = 'participant_country';
    public const PARTICIPANT_WEBSITE         = 'participant_website';
    public const PARTICIPANT_GENDER          = 'participant_gender';
    public const PARTICIPANT_ARRIVAL_DATE    = 'participant_arrival_date';
    public const PARTICIPANT_DEPARTURE_DATE  = 'participant_departure_date';
    public const BILLING_NAME                = 'billing_name';
    public const BILLING_ADDRESS             = 'billing_address';
    public const BILLING_CITY                = 'billing_city';
    public const BILLING_ZIPCODE             = 'billing_zipcode';
    public const BILLING_COUNTRY             = 'billing_country';
    public const BILLING_PHONE               = 'billing_phone';
    public const BILLING_EMAIL               = 'billing_email';
    public const BILLING_ORGANIZATION        = 'billing_organization';
    public const BILLING_VAT_NUMBER          = 'billing_vat_number';
    public const BILLING_EXTRA               = 'billing_extra';
    public const SHEET_TITLE                 = 'sheet_title';
    public const SHEET_ORGANIZATION          = 'sheet_organization';
    public const SHEET_ORGANIZATION_CATEGORY = 'sheet_organization_category';
    public const SHEET_ORGANIZATION_TURNOVER = 'sheet_organization_turnover';
    public const SHEET_ORGANIZATION_STAFF    = 'sheet_organization_staff';
    public const SHEET_ADDRESS               = 'sheet_address';
    public const SHEET_ZIPCODE               = 'sheet_zipcode';
    public const SHEET_CITY                  = 'sheet_city';
    public const SHEET_COUNTRY               = 'sheet_country';
    public const SHEET_WEBSITE               = 'sheet_website';
    public const SHEET_PHONE                 = 'sheet_phone';
    public const SHEET_DESCRIPTION           = 'sheet_description';
    public const SHEET_LOGO                  = 'sheet_logo';
    public const SHEET_APPLICATION_DOMAIN    = 'sheet_application_domain';

    // Setter
    public const PARTICIPANT_DATA = 'participant_data';
    public const SHEET_DATA       = 'sheet_data';

    public const SHEET_TEMPLATE_TAGS = [
        self::SHEET_LOGO,
    ];

    /**
     * @return array
     */
    public static function getAll(): array
    {
        return array_merge(
            self::getParticipantTags(),
            self::getBillingTags(),
            self::getSheetTags()
        );
    }

    /**
     * @return array
     */
    public static function getSetters(): array
    {
        return [
            self::PARTICIPANT_DATA,
            self::SHEET_DATA,
        ];
    }

    /**
     * @return array
     */
    public static function getParticipantIdentityTags(): array
    {
        return [
            self::PARTICIPANT_FIRSTNAME,
            self::PARTICIPANT_LASTNAME,
        ];
    }

    public static function getParticipantTags(): array
    {
        return
            array_merge(
                [
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
                    self::PARTICIPANT_ARRIVAL_DATE,
                    self::PARTICIPANT_DEPARTURE_DATE,
                ],
                self::getGenericParticipantTags()
            )
        ;
    }

    /**
     * @return array
     */
    public static function getBillingTags(): array
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
    public static function getSheetTags(): array
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
            self::SHEET_DESCRIPTION,
            self::SHEET_LOGO,
            self::SHEET_APPLICATION_DOMAIN,
        ];
    }

    /**
     * @return array
     */
    public static function getSeeableTags(): array
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
            self::SHEET_DESCRIPTION,
            self::SHEET_LOGO,
            self::PARTICIPANT_POSITION,
            self::SHEET_APPLICATION_DOMAIN,
        ];
    }

    /**
     * @return array
     */
    public static function getGenericSheetTags(): array
    {
        $genericTags = [];

        for ($i = 1; $i <= self::GENERIC_TAGS_NUMBER; ++$i) {
            $genericTags[] = 'sheet_generic_tag_' . $i;
        }

        return $genericTags;
    }

    public static function getGenericParticipantTags(): array
    {
        $genericTags = [];

        for ($i = 1; $i <= self::PARTICIPANTS_GENERIC_TAGS_NUMBER; ++$i) {
            $genericTags[] = 'participant_generic_tag_' . $i;
        }

        return $genericTags;
    }

    /**
     * Theses tags are used on the sheet to populate info from outside of the registration to the sheet
     *
     * @return array
     */
    public static function getGenericSheetTemplateTags(): array
    {
        $genericSheetTemplateTags = [];

        for ($i = 1; $i <= self::GENERIC_TAGS_NUMBER; ++$i) {
            $genericSheetTemplateTags[] = 'sheet_template_generic_tag_' . $i;
        }

        return $genericSheetTemplateTags;
    }

    /**
     * @return array
     */
    public static function getSheetAndGenericTags(): array
    {
        return array_merge(self::getSheetTags(), self::getGenericSheetTags());
    }

    public static function getSheetAndGenericSheetTagsAndGenericSheetTemplateTags(): array
    {
        return array_merge(self::getSheetTags(), self::getGenericSheetTags(), self::getGenericSheetTemplateTags());
    }

    /**
     * @return array
     */
    public static function getSheetParticipantGenericAndSettersTags(): array
    {
        return array_merge(
            self::getSheetTags(),
            self::getParticipantTags(),
            self::getGenericSheetTags(),
            self::getSetters()
        );
    }

    public static function getRegistrationTemplateTagView(): TemplateTagView
    {
        return self::getTemplateTagView();
    }

    public static function getTemplateTagView(): TemplateTagView
    {
        return new TemplateTagView(
            self::getSheetParticipantGenericAndSettersTags(),
            self::PARTICIPANT_DATA,
            self::getParticipantTags(),
            self::SHEET_DATA,
            self::getSheetAndGenericTags()
        );
    }
}

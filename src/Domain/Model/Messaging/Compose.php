<?php

namespace Proximum\Vimeet\Domain\Model\Messaging;

class Compose
{
    public const TAG_EVENT_NAME         = '%event%';
    public const TAG_PARTICIPANT        = '%participant%';
    public const TAG_PARTICIPATION_TYPE = '%participationType%';
    public const TAG_SHEET_PLANNING     = '%sheetPlanning%';
    public const TAG_SHEET_SPOT         = '%sheetSpot%';

    // CTA
    public const TAG_CTA_AGENDA_CONFIRMATION = '%agendaConfirmationCTA%';
    public const TAG_CTA_EBADGE = '%downloadEbadgeCTA%';
    public const TAG_CTA_TEST_VISIO_CONFIGURATION = '%testVisioConfigurationCTA%';

    public const LINK_SHEET                 = '%sheetLink%';
    public const LINK_PACKAGE               = '%packageLink%';
    public const LINK_ORDERS                = '%ordersLink%';
    public const LINK_AGENDA                = '%agendaLink%';
    public const LINK_PROGRAM               = '%programLink%';
    public const LINK_CATALOG               = '%catalogLink%';
    public const LINK_MEETING_REQUEST       = '%meetingRequestLink%';
    public const LINK_ACTIVACTE_ACCOUNT     = '%activateAccountLink%';
    public const LINK_EXPORT_MEETING_SHEET  = '%exportMeetingSheetLink%';
    public const LINK_VALIDATE_MOBILE_PHONE = '%validateMobilePhoneLink%';

    public const LINK_PLACEHOLDERS = [
        self::LINK_ACTIVACTE_ACCOUNT,
        self::LINK_AGENDA,
        self::LINK_CATALOG,
        self::LINK_MEETING_REQUEST,
        self::LINK_ORDERS,
        self::LINK_PACKAGE,
        self::LINK_PROGRAM,
        self::LINK_SHEET,
        self::LINK_EXPORT_MEETING_SHEET,
        self::LINK_VALIDATE_MOBILE_PHONE,
    ];

    public const TAG_PLACEHOLDERS = [
        self::TAG_EVENT_NAME,
        self::TAG_PARTICIPANT,
        self::TAG_PARTICIPATION_TYPE,
        self::TAG_SHEET_PLANNING,
        self::TAG_SHEET_SPOT,
        self::TAG_CTA_AGENDA_CONFIRMATION,
        self::TAG_CTA_EBADGE,
        self::TAG_CTA_TEST_VISIO_CONFIGURATION,
    ];

    /**
     * @return string[]
     */
    public static function getAllPlaceholders(): array
    {
        return array_merge(self::TAG_PLACEHOLDERS, self::LINK_PLACEHOLDERS);
    }

    /**
     * @return string[]
     */
    private static function getTagPlaceholders(): array
    {
        return self::TAG_PLACEHOLDERS;
    }

    /**
     * @return string[]
     */
    private static function getLinkPlaceholders(): array
    {
        return self::LINK_PLACEHOLDERS;
    }
}

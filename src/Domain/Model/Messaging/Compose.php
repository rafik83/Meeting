<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Messaging;

class Compose
{
    const TAG_EVENT_NAME         = '%event%';
    const TAG_PARTICIPANT        = '%participant%';
    const TAG_PARTICIPATION_TYPE = '%participationType%';
    const TAG_SHEET_PLANNING     = '%sheetPlanning%';

    // CTA
    const TAG_CTA_AGENDA_CONFIRMATION = '%agendaConfirmationCTA%';

    const LINK_SHEET                 = '%sheetLink%';
    const LINK_PACKAGE               = '%packageLink%';
    const LINK_ORDERS                = '%ordersLink%';
    const LINK_AGENDA                = '%agendaLink%';
    const LINK_PROGRAM               = '%programLink%';
    const LINK_CATALOG               = '%catalogLink%';
    const LINK_MEETING_REQUEST       = '%meetingRequestLink%';
    const LINK_ACTIVACTE_ACCOUNT     = '%activateAccountLink%';
    const LINK_EXPORT_MEETING_SHEET  = '%exportMeetingSheetLink%';
    const LINK_VALIDATE_MOBILE_PHONE = '%validateMobilePhoneLink%';

    /**
     * @return string[]
     */
    public static function getAllPlaceholders()
    {
        return array_merge(self::getTagPlaceholders(), self::getLinkPlaceholders());
    }

    /**
     * @return string[]
     */
    private static function getTagPlaceholders()
    {
        return [
            self::TAG_EVENT_NAME,
            self::TAG_PARTICIPANT,
            self::TAG_PARTICIPATION_TYPE,
            self::TAG_SHEET_PLANNING,
            self::TAG_CTA_AGENDA_CONFIRMATION,
        ];
    }

    /**
     * @return string[]
     */
    private static function getLinkPlaceholders()
    {
        return [
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
    }
}

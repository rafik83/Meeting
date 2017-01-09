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
    const TAG_PARTICIPATION_TYPE = '%participationType';

    const LINK_SHEET             = '%sheetLink%';
    const LINK_PACKAGE           = '%packageLink%';
    const LINK_ORDERS            = '%ordersLink%';
    const LINK_AGENDA            = '%agendaLink%';
    const LINK_PROGRAM           = '%programLink%';
    const LINK_CATALOG           = '%catalogLink%';
    const LINK_MEETING_REQUEST   = '%meetingRequestLink%';
    const LINK_ACTIVACTE_ACCOUNT = '%activateAccountLink%';
}

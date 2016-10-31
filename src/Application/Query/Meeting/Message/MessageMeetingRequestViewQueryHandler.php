<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting\Message;

use Proximum\Vimeet\Application\View\Meeting\Message\MessageMeetingRequestView;

class MessageMeetingRequestViewQueryHandler
{
    /**
     * @param MessageMeetingRequestViewQuery $query
     *
     * @return MessageMeetingRequestView
     */
    public function handle(MessageMeetingRequestViewQuery $query)
    {
        return new MessageMeetingRequestView(
            $query->message->getFrom()->getId(),
            $query->sheetName,
            $query->message->getContent(),
            $query->message->getCreatedAt(),
            $query->message->getFrom() === $query->meetingRequest->getFromSheet() ? MessageMeetingRequestView::LEFT : MessageMeetingRequestView::RIGHT
        );
    }
}

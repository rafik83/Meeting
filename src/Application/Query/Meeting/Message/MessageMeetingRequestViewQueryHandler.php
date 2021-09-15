<?php

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
            $query->message->getFrom(),
            $query->sheetName,
            $query->message->getContent(),
            $query->message->getCreatedAt(),
            $query->meetingRequest->isSender($query->message->getFrom()) ? MessageMeetingRequestView::LEFT : MessageMeetingRequestView::RIGHT
        );
    }
}

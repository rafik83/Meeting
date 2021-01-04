<?php

namespace Proximum\Vimeet\Application\View\Meeting\Message;

use Proximum\Vimeet\Domain\Model\Sheet;

class DiscussionMeetingRequestView
{
    /**
     * @var MessageMeetingRequestView[]
     */
    public $messages = [];

    /**
     * @param MessageMeetingRequestView $message
     */
    public function addMessage(MessageMeetingRequestView $message)
    {
        $this->messages[] = $message;
    }

    /**
     * @return bool
     */
    public function hasMessages()
    {
        return !empty($this->messages);
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasMessageOfSheet(Sheet $sheet)
    {
        return !empty(array_filter($this->messages, function (MessageMeetingRequestView $message) use ($sheet) {
            return $message->sheet === $sheet;
        }));
    }
}

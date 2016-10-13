<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\Message;

class DiscussionMeetingRequestView
{
    /**
     * @var MessageMeetingRequestView
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
}

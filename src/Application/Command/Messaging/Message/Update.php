<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Message;

use Proximum\Vimeet\Domain\Model\Messaging\Message;

final class Update
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $content;

    /**
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->name    = $message->getName();
        $this->subject = $message->getSubject();
        $this->content = $message->getContent();
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}

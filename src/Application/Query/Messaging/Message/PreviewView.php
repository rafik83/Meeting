<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Messaging\Message;


use Proximum\Vimeet\Domain\Model\Messaging\Message;

class PreviewView
{
    /** @var string */
    public $subject;

    /** @var string */
    public $content;

    /**
     * @param string $subject
     * @param string $content
     */
    public function __construct($subject, $content)
    {
        $this->subject = $subject;
        $this->content = $content;
    }

    /**
     * @param Message $message
     *
     * @return PreviewView
     */
    public static function createFromMessage(Message $message)
    {
        return new self($message->getSubject(), $message->getContent());
    }
}

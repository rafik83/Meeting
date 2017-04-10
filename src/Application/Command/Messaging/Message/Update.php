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
     * @var array
     */
    public $translations;

    /**
     * @var Message
     */
    private $message;

    /**
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->name    = $message->getName();

        foreach ($this->message->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'subject' => $translation->getSubject(),
                'content' => $translation->getContent(),
                'locale'  => $translation->getLocale(),
            ];
        }
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}

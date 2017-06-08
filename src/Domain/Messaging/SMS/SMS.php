<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\SMS;

class SMS
{
    /**
     * Receiver phone number
     * @var string
     */
    private $receiver;

    /** @var string body */
    private $message;

    /**
     * @param string $receiver
     * @param string $message
     */
    public function __construct($receiver, $message)
    {
        $this->receiver = $receiver;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}

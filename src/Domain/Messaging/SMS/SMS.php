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

    /** @var \DateTimeInterface */
    private $date;

    /**
     * @param string             $receiver
     * @param string             $message
     * @param \DateTimeInterface $date
     */
    public function __construct($receiver, $message, \DateTimeInterface $date)
    {
        $this->receiver = $receiver;
        $this->message = $message;
        $this->date = $date;
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

    /**
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }
}

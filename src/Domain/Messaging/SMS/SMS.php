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
     *
     * @var string
     */
    private $receiver;

    /** @var string body */
    private $message;

    /** @var bool */
    private $isAdvertising;

    /**
     * @param string $receiver
     * @param string $message
     * @param bool   $isAdvertising
     */
    public function __construct($receiver, $message, bool $isAdvertising = true)
    {
        $this->receiver = $receiver;
        $this->message = $message;
        $this->isAdvertising = $isAdvertising;
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
     * @return bool
     */
    public function isAdvertising(): bool
    {
        return $this->isAdvertising;
    }
}

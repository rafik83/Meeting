<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter\SMS;

use Twilio\Rest\Client;

class TwilioClient
{
    /** @var */
    private $twilioSID;

    /** @var */
    private $twilioToken;

    /** @var string */
    private $twilioNumber;

    public function __construct(
        string $twilioSID,
        string $twilioToken,
        string $twilioNumber
    ) {
        $this->twilioSID = $twilioSID;
        $this->twilioToken = $twilioToken;
        $this->twilioNumber = $twilioNumber;
    }

    public function getClient(): Client
    {
        return new Client($this->twilioSID, $this->twilioToken);
    }

    public function sendMessage(string $to, string $message): void
    {
        $client = $this->getClient();

        $client->messages->create($to, [
            'from' => $this->twilioNumber,
            'body' => $message,
        ]);
    }
}

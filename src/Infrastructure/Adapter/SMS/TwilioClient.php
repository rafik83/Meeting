<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter\SMS;

use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Twilio\Rest\Client;

class TwilioClient implements SMSProviderInterface
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

    private function getClient(): Client
    {
        return new Client($this->twilioSID, $this->twilioToken);
    }

    public function canSend(SMS $sms): bool
    {
        return mb_strpos($sms->getReceiver(), '+1') === 0;
    }

    public function sendMessage(SMS $sms): void
    {
        $client = $this->getClient();

        $client->messages->create($sms->getReceiver(), [
            'from' => $this->twilioNumber,
            'body' => $sms->getMessage(),
        ]);
    }
}

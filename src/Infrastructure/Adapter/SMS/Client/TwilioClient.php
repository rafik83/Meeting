<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\SMS\Client;

use Twilio\Rest\Api\V2010\Account\MessageList;
use Twilio\Rest\Client;

class TwilioClient
{
    /** @var */
    private $twilioSID;

    /** @var */
    private $twilioToken;

    public function __construct(
        string $twilioSID,
        string $twilioToken
    ) {
        $this->twilioSID = $twilioSID;
        $this->twilioToken = $twilioToken;
    }

    public function getClient(): Client
    {
        return new Client($this->twilioSID, $this->twilioToken);
    }

    public function getMessageList(): MessageList
    {
        return $this->getClient()->messages;
    }
}

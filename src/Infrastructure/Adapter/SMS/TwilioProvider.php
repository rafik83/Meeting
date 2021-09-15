<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\SMS;

use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\Client\TwilioClient;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\Exception\ProviderNotAbleToSendThisTypeOfSMSException;

class TwilioProvider implements SMSProviderInterface
{
    /** @var string */
    private $twilioNumber;

    /** @var TwilioClient */
    private $twilioClient;

    public function __construct(
        TwilioClient $twilioClient,
        string $twilioNumber
    ) {
        $this->twilioNumber = $twilioNumber;
        $this->twilioClient = $twilioClient;
    }

    public function canSend(SMS $sms): bool
    {
        return mb_strpos($sms->getReceiver(), '+1') === 0;
    }

    public function sendMessage(SMS $sms): void
    {
        if (!$this->canSend($sms)) {
            throw new ProviderNotAbleToSendThisTypeOfSMSException(sprintf('%s', $sms->getReceiver()));
        }

        $this->twilioClient->getMessageList()->create($sms->getReceiver(), [
            'from' => $this->twilioNumber,
            'body' => $sms->getMessage(),
        ]);
    }
}

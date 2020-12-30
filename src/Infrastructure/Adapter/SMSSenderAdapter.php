<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\InvalidReceiverException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\Exception\NoProviderAvailableException;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\SMSProviderGuesser;

class SMSSenderAdapter implements SMSSenderInterface
{
    /** @var SMSProviderGuesser */
    private $SMSProviderGuesser;

    public function __construct(SMSProviderGuesser $SMSProviderGuesser)
    {
        $this->SMSProviderGuesser = $SMSProviderGuesser;
    }

    /**
     * {@inheritdoc}
     *
     * @throws FailToSendSMSException
     * @throws InvalidReceiverException
     * @throws NoProviderAvailableException
     */
    public function send(SMS $sms)
    {
        $provider = $this->SMSProviderGuesser->guessProvider($sms);

        $provider->sendMessage($sms);
    }
}

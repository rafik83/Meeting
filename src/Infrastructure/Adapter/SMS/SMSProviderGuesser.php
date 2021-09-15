<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\SMS;

use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\Exception\NoProviderAvailableException;

class SMSProviderGuesser
{
    /** @var SMSProviders */
    private $SMSProviders;

    public function __construct(SMSProviders $SMSProviders)
    {
        $this->SMSProviders = $SMSProviders;
    }

    /**
     * @param SMS $sms
     *
     * @return SMSProviderInterface
     *
     * @throws NoProviderAvailableException
     */
    public function guessProvider(SMS $sms): SMSProviderInterface
    {
        foreach ($this->SMSProviders->getProviders() as $provider) {
            if ($provider->canSend($sms)) {
                return $provider;
            }
        }

        throw new NoProviderAvailableException(
            'No provider available to handle this message'
        );
    }
}

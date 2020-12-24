<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\SMS;

use Proximum\Vimeet\Infrastructure\Adapter\SMS\Exception\SMSProviderAlreadyRegisteredException;

class SMSProviders
{
    private $registeredProviders;

    public function __construct()
    {
        $this->registeredProviders = [];
    }

    /**
     * @param SMSProviderInterface $provider
     *
     * @throws SMSProviderAlreadyRegisteredException
     */
    public function registerProvider(SMSProviderInterface $provider): void
    {
        if (isset($this->registeredProviders[get_class($provider)])) {
            throw new SMSProviderAlreadyRegisteredException(
                sprintf('This provider %s is already registered', get_class($provider))
            );
        }

        $this->registeredProviders[get_class($provider)] = $provider;
    }

    /**
     * @return SMSProviderInterface[]
     */
    public function getProviders(): array
    {
        return $this->registeredProviders;
    }
}

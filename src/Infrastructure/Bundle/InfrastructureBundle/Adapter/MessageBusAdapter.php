<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use Proximum\Vimeet\Application\Adapter\MessageBusInterface;
use Symfony\Component\Messenger\MessageBusInterface as SymfonyMessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class MessageBusAdapter implements MessageBusInterface
{
    private SymfonyMessageBusInterface $bus;

    public function __construct(SymfonyMessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function dispatch($message): void
    {
        $this->bus->dispatch($message);
    }

    /** {@inheritDoc} */
    public function dispatchDelayed($message, int $delay): void
    {
        $delayStamp = new DelayStamp($delay * 1000);

        $this->bus->dispatch($message, [$delayStamp]);
    }
}

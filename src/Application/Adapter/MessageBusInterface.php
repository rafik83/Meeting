<?php

namespace Proximum\Vimeet\Application\Adapter;

interface MessageBusInterface
{
    public function dispatch($message): void;

    /**
     * @param int $delay in seconds
     */
    public function dispatchDelayed($message, int $delay): void;
}

<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Happening;

interface WebinarNotificationPublisherInterface
{
    public function send(Happening $happening, string $type, array $data): void;
}

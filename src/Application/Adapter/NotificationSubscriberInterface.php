<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Happening;

interface NotificationSubscriberInterface
{
    public function getUrl(): string;
    public function getHappeningSubscriberKey(Happening $happening, array $types): string;
}

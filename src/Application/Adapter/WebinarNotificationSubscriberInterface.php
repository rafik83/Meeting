<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Happening;

interface WebinarNotificationSubscriberInterface
{
    public function getUrl(): string;
    public function getSubscriberKey(Happening $happening, $type): string;
}

<?php

namespace Proximum\Vimeet\Domain\Happening\Webinar\Broadcast;

interface Broadcast
{
    public function getBroadcastId(): string;

    public function getHlsUrl(): ?string;
}

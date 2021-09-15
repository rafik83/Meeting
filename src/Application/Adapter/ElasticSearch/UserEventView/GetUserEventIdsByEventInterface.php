<?php

namespace Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView;

use Proximum\Vimeet\Domain\Model\Event;

interface GetUserEventIdsByEventInterface
{
    /**
     * @return string[] elasticsearch document id
     */
    public function handle(Event $event): array;
}

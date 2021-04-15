<?php

namespace Proximum\Vimeet\Domain\Repository\Event;

use Proximum\Vimeet\Domain\Model\Event;

interface CustomLinkRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return Event\CustomLink[]
     */
    public function findByEvent(Event $event): array;
}

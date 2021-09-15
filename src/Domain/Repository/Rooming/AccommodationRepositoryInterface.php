<?php

namespace Proximum\Vimeet\Domain\Repository\Rooming;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;

interface AccommodationRepositoryInterface
{
    public function add(Accommodation $accommodation): void;

    public function update(Accommodation $accommodation): void;

    /**
     * @param Event $event
     *
     * @return Accommodation[]
     */
    public function getByEvent(Event $event): array;
}

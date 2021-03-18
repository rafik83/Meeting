<?php

namespace Proximum\Vimeet\Domain\Repository\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;

interface SpeakerRepositoryInterface
{
    /**
     * @param Speaker $speaker
     */
    public function add(Speaker $speaker);

    /**
     * @param Speaker $speaker
     */
    public function set(Speaker $speaker);

    /**
     * @param Speaker $speaker
     */
    public function remove(Speaker $speaker);

    /**
     * @param Event $event
     *
     * @return Speaker[]
     */
    public function allByEvent(Event $event);
}

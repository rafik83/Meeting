<?php

namespace Proximum\Vimeet\Domain\Repository\Rooming;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;
use Proximum\Vimeet\Domain\View\Rooming\StayView;

interface StayRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return StayView[]
     */
    public function getStaysByEvent(Event $event): array;

    public function add(Stay $stay): void;
}

<?php

namespace Proximum\Vimeet\Domain\Repository\Analytic;

use Proximum\Vimeet\Domain\Model\Analytic\MeetingSolution;
use Proximum\Vimeet\Domain\Model\Event;

interface MeetingSolutionRepositoryInterface
{
    /**
     * @param $meetingSolution
     */
    public function add(MeetingSolution $meetingSolution);

    /**
     * @param Event $event
     *
     * @return MeetingSolution[]
     */
    public function findByEvent(Event $event): array;
}
